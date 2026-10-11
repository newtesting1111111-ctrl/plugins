import { defineConfig } from "vite";
import { fileURLToPath, URL } from "node:url";

export default defineConfig({
  build: {
    rollupOptions: {
      input: {
        home: fileURLToPath(new URL("./index.html", import.meta.url)),
        seo: fileURLToPath(new URL("./seo/index.html", import.meta.url)),
        ppc: fileURLToPath(new URL("./ppc/index.html", import.meta.url)),
      },
    },
  },
});
