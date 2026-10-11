"""Exercise real PHP mail() using a local capture program; no emails are sent."""
import concurrent.futures
import email.policy
from email.parser import BytesParser
import json
import os
from pathlib import Path
import shlex
import shutil
import signal
import socket
import subprocess
import sys
import tempfile
import time
import unittest
from urllib.error import HTTPError, URLError
from urllib.parse import urlencode
from urllib.request import Request, urlopen


class MailHandlerTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.php = os.environ.get("PHP_BIN") or shutil.which("php")
        if not cls.php:
            raise RuntimeError("PHP CLI is required. Install PHP or set PHP_BIN.")

    def setUp(self):
        self.temp = tempfile.TemporaryDirectory(prefix="uweb-mail-test-")
        self.root = Path(self.temp.name)
        docroot = self.root / "public"
        docroot.mkdir()
        (self.root / "private").mkdir()
        shutil.copyfile(Path(__file__).resolve().parents[1] / "public/send-mail.php",
                        docroot / "send-mail.php")
        capture = self.root / "capture.py"
        capture.write_text("""import fcntl, json, pathlib, sys
root = pathlib.Path(__file__).parent
if (root / 'fail').exists():
    sys.exit(1)
mail = sys.stdin.read()
with (root / 'messages.jsonl').open('a') as output:
    fcntl.flock(output, fcntl.LOCK_EX)
    output.write(json.dumps({'args': sys.argv[1:], 'mail': mail}) + '\\n')
""", encoding="utf-8")
        with socket.socket() as sock:
            sock.bind(("127.0.0.1", 0))
            port = sock.getsockname()[1]
        self.base = f"http://127.0.0.1:{port}"
        env = dict(os.environ, PHP_CLI_SERVER_WORKERS="4")
        self.server = subprocess.Popen([
            self.php, "-n", "-d", f"sys_temp_dir={self.root / 'private'}",
            "-d", f"sendmail_path={shlex.quote(sys.executable)} {shlex.quote(str(capture))}",
            "-S", f"127.0.0.1:{port}", "-t", str(docroot),
        ], env=env, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL,
            start_new_session=True)
        for _ in range(50):
            try:
                if self.request(method="GET")[0] == 405:
                    return
            except URLError:
                time.sleep(0.05)
        self.tearDown()
        raise RuntimeError("PHP test server failed to start.")

    def tearDown(self):
        if self.server.poll() is None:
            os.killpg(self.server.pid, signal.SIGTERM)
            self.server.wait(timeout=5)
        self.temp.cleanup()

    def payload(self, **changes):
        return dict({"name": "Анна", "contact": "anna@example.com", "service": "Лендинг",
                     "message": "Нужен сайт для услуг & рекламы.", "website": ""}, **changes)

    def request(self, payload=None, method="POST", headers=None, raw=None, form=False):
        request_headers = {"Accept": "application/json", "Content-Type": "application/json"}
        if form:
            request_headers = {"Accept": "text/html", "Content-Type": "application/x-www-form-urlencoded"}
            raw = urlencode(payload or self.payload()).encode()
        request_headers.update(headers or {})
        if raw is None and method == "POST":
            raw = json.dumps(payload or self.payload(), ensure_ascii=False).encode()
        request = Request(self.base + "/send-mail.php", data=raw, method=method,
                          headers=request_headers)
        try:
            response = urlopen(request, timeout=8)
        except HTTPError as error:
            response = error
        with response:
            return response.status, response.read().decode(), dict(response.headers)

    def messages(self):
        file = self.root / "messages.jsonl"
        return [json.loads(line) for line in file.read_text().splitlines()] if file.exists() else []

    def test_utf8_content_and_fixed_addresses(self):
        status, body, _ = self.request(self.payload(to="attacker@example.com", sender="evil@example.com"),
                                       headers={"Origin": self.base})
        self.assertEqual(status, 200)
        self.assertTrue(json.loads(body)["ok"])
        captured = self.messages()
        self.assertEqual(len(captured), 1)
        mail = BytesParser(policy=email.policy.default).parsebytes(captured[0]["mail"].encode())
        self.assertEqual(mail["To"], "sitehub24@gmail.com")
        self.assertEqual(mail["From"].addresses[0].addr_spec, "info@uweb.by")
        self.assertEqual(mail["From"].addresses[0].display_name, "uweb.by")
        self.assertEqual(mail["Reply-To"], "anna@example.com")
        self.assertIn("-finfo@uweb.by", captured[0]["args"])
        self.assertIn("uweb.by", str(mail["Subject"]))
        self.assertIn("Формат: Лендинг", mail.get_content())
        self.assertIn("Нужен сайт для услуг & рекламы.", mail.get_content())
        self.assertIn("(Минск)", mail.get_content())

    def test_phone_contact_does_not_create_email_header(self):
        self.assertEqual(self.request(self.payload(contact="+375255032030"))[0], 200)
        mail = BytesParser(policy=email.policy.default).parsebytes(self.messages()[0]["mail"].encode())
        self.assertIsNone(mail["Reply-To"])

    def test_seo_formats_include_site_and_distinct_subject(self):
        services = ["Комплексное SEO", "Локальное SEO", "SEO интернет-магазина", "Технический аудит"]
        for service in services:
            with self.subTest(service=service):
                status, body, _ = self.request(self.payload(
                    context="seo", service=service, site="https://example.by/catalog?category=1&sort=name"))
                self.assertEqual(status, 200)
                self.assertTrue(json.loads(body)["ok"])
        for captured, service in zip(self.messages(), services):
            mail = BytesParser(policy=email.policy.default).parsebytes(captured["mail"].encode())
            self.assertEqual(str(mail["Subject"]), "Заявка на SEO — uweb.by")
            self.assertEqual(mail["To"], "sitehub24@gmail.com")
            self.assertEqual(mail["From"].addresses[0].addr_spec, "info@uweb.by")
            self.assertIn("Заявка на SEO-продвижение", mail.get_content())
            self.assertIn("Формат: " + service, mail.get_content())
            self.assertIn("Сайт: https://example.by/catalog?category=1&sort=name", mail.get_content())

    def test_invalid_seo_context_and_site_are_rejected(self):
        for fields in [{"context": "other"}, {"context": ["seo"]},
                       {"context": "seo", "service": "Лендинг"},
                       {"service": "Комплексное SEO"}, {"site": ["https://example.by"]},
                       {"site": "javascript:alert(1)"}, {"site": "ftp://example.by"},
                       {"site": "example.by"}, {"site": "https://example.by/" + "a" * 2048},
                       {"site": "https://example.by/\nBcc: attacker@example.com"}]:
            with self.subTest(fields=fields):
                self.assertEqual(self.request(self.payload(**fields))[0], 422)
        self.assertEqual(self.messages(), [])

    def test_seo_regular_post_returns_to_seo_page(self):
        status, body, _ = self.request(self.payload(context="seo", service="Локальное SEO"), form=True)
        self.assertEqual(status, 200)
        self.assertIn('href="/seo/#contact"', body)
        self.assertEqual(len(self.messages()), 1)

    def test_ppc_tariffs_have_their_own_subject_and_service(self):
        services = ["Контекст Start", "Контекст Standart", "Контекст Pro", "Индивидуальный расчёт"]
        for service in services:
            with self.subTest(service=service):
                self.assertEqual(self.request(self.payload(
                    context="ppc", service=service, site="https://example.by/", message="Реклама в Минске."))[0], 200)
        for captured, service in zip(self.messages(), services):
            mail = BytesParser(policy=email.policy.default).parsebytes(captured["mail"].encode())
            self.assertEqual(str(mail["Subject"]), "Заявка на PPC — uweb.by")
            self.assertEqual(mail["To"], "sitehub24@gmail.com")
            self.assertEqual(mail["From"].addresses[0].addr_spec, "info@uweb.by")
            self.assertIn("Заявка на контекстную рекламу", mail.get_content())
            self.assertIn("Формат: " + service, mail.get_content())
            self.assertIn("Сайт: https://example.by/", mail.get_content())

    def test_ppc_native_post_and_service_group_validation(self):
        self.assertEqual(self.request(self.payload(context="ppc", service="Лендинг"))[0], 422)
        self.assertEqual(self.request(self.payload(context="seo", service="Контекст Start"))[0], 422)
        self.assertEqual(self.request(self.payload(service="Контекст Start"))[0], 422)
        status, body, _ = self.request(self.payload(context="ppc", service="Контекст Pro"), form=True)
        self.assertEqual(status, 200)
        self.assertIn('href="/ppc/#contact"', body)
        self.assertEqual(len(self.messages()), 1)

    def test_invalid_fields_and_header_injection_are_rejected(self):
        for fields in [{"name": " "}, {"contact": ""}, {"name": ["Анна"]},
                       {"name": "а" * 81}, {"contact": "а" * 161},
                       {"message": "а" * 3001}, {"service": "Другой"},
                       {"contact": "anna@example.com\r\nBcc: attacker@example.com"}]:
            with self.subTest(fields=fields):
                self.assertEqual(self.request(self.payload(**fields))[0], 422)
        self.assertEqual(self.messages(), [])

    def test_large_and_malformed_requests_are_rejected(self):
        self.assertEqual(self.request(raw=b"{")[0], 400)
        self.assertEqual(self.request(raw=b"x" * 16385)[0], 413)
        self.assertEqual(self.request(raw=b"test", headers={"Content-Type": "text/plain"})[0], 415)
        self.assertEqual(self.messages(), [])

    def test_honeypot_cross_origin_and_get_do_not_send(self):
        self.assertEqual(self.request(self.payload(website="spam.example"))[0], 200)
        self.assertEqual(self.request(headers={"Origin": "https://other.example"})[0], 403)
        self.assertEqual(self.request(headers={"Sec-Fetch-Site": "cross-site"})[0], 403)
        status, _, headers = self.request(method="GET")
        self.assertEqual(status, 405)
        self.assertEqual(headers["Allow"], "POST")
        self.assertEqual(self.messages(), [])

    def test_concurrent_rate_limit(self):
        with concurrent.futures.ThreadPoolExecutor(max_workers=8) as pool:
            responses = list(pool.map(lambda _: self.request(), range(8)))
        self.assertEqual(sorted(response[0] for response in responses), [200] * 5 + [429] * 3)
        self.assertEqual(len(self.messages()), 5)
        self.assertTrue(all(response[2]["Retry-After"] == "600"
                            for response in responses if response[0] == 429))

    def test_mail_failure_is_reported_without_success(self):
        (self.root / "fail").touch()
        status, body, _ = self.request()
        self.assertEqual(status, 502)
        self.assertFalse(json.loads(body)["ok"])
        self.assertEqual(self.messages(), [])

    def test_regular_post_works_without_javascript(self):
        status, body, headers = self.request(form=True)
        self.assertEqual(status, 200)
        self.assertIn("text/html", headers["Content-Type"])
        self.assertIn("Заявка отправлена", body)
        self.assertIn('href="/#contact"', body)
        self.assertEqual(len(self.messages()), 1)


if __name__ == "__main__":
    unittest.main()
