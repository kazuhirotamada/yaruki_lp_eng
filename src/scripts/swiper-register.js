// src/scripts/swiper-register.js
import { register } from 'swiper/element/bundle';

// 既に登録済みなら二重登録しない
if (!customElements.get('swiper-container')) {
  register();
}