const form=document.querySelector('#project-form');
const status=document.querySelector('#form-status');
const button=form.querySelector('button[type=submit]');
const label=document.querySelector('#submit-label');
document.querySelectorAll('[data-service]').forEach(link=>link.addEventListener('click',()=>{form.elements.service.value=link.dataset.service;}));
form.addEventListener('submit',async event=>{
 event.preventDefault();
 if(button.disabled)return;
 for(const name of ['name','contact'])form.elements[name].value=form.elements[name].value.trim();
 if(!form.reportValidity())return;
 const payload=Object.fromEntries(new FormData(form));
 button.disabled=true;form.setAttribute('aria-busy','true');label.textContent='Отправляем…';status.textContent='';
 const controller=new AbortController();const timeout=setTimeout(()=>controller.abort(),15000);
 try{
  const response=await fetch(form.action,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify(payload),signal:controller.signal});
  const result=await response.json().catch(()=>null);
  if(!result)throw new Error("Сервер не смог обработать заявку. Напишите в Telegram или попробуйте позже.");
  if(!response.ok||result.ok!==true)throw new Error(result.message||'Не удалось отправить заявку.');
  status.textContent=result.message||'Спасибо! Заявка отправлена.';status.dataset.state='success';form.reset();
 }catch(error){status.textContent=error.name==='AbortError'?'Сервер не ответил вовремя. Напишите в Telegram или попробуйте позже.':(error.message==='Failed to fetch'?'Нет связи с сервером. Попробуйте позже или напишите в Telegram.':error.message);status.dataset.state='error';}
 finally{clearTimeout(timeout);button.disabled=false;form.removeAttribute('aria-busy');label.textContent='Получить расчёт стоимости';}
});
