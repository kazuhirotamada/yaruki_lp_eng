// astro.config.mjs / .ts
import { defineConfig } from 'astro/config';
import react from '@astrojs/react';

export default defineConfig({
  vite: {
    resolve: { alias: { '@': '/src' } },
    css: { devSourcemap: true },
    // build: {
    //   minify: false, // ← 圧縮を無効化
    // },
    // esbuild: {
    //   minify: false, // ← JSのminifyも無効化
    // },
  },
  integrations: [
    react({
        include: ['**/*.{jsx,tsx}'], // どこに置いてもOK
    }),
  ], // ← includeを指定しない
});