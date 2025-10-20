// astro.config.mjs / .ts
import { defineConfig } from 'astro/config';
import react from '@astrojs/react';

export default defineConfig({
  vite: {
    css: { devSourcemap: true },
  },
  integrations: [
    react({
        include: ['**/*.{jsx,tsx}'], // どこに置いてもOK
    }),
  ], // ← includeを指定しない
});