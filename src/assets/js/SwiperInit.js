// import Swiper bundle with all modules installed
import Swiper from 'swiper/bundle';
// import styles bundle
import 'swiper/css/bundle';

export default function SwiperInit() {
    // init Swiper:
    const swiperUpper = new Swiper('.upperSwiper', {
        allowTouchMove: false,
        autoplay: {
            delay: 10000,
        },
        // Optional parameters
        // direction: 'horizontal',
        effect: 'fade',
        fadeEffect: { crossFade: true },
        speed: 6000,
        loop: true,
        slidesPerGroup: 2,
        slidesPerView: 2,

        breakpoints: {
            999: {
                slidesPerGroup: 1,
                slidesPerView: 1,
            }
        }
        
    });
    
    // init Swiper:
    const swiperLower = new Swiper('.lowerSwiper', {
        allowTouchMove: false,
        
        // Optional parameters
        direction: 'horizontal',
        simulateTouch: false,      // ← クリック/タッチの擬似処理を無効
        shortSwipes: false,        // ← どんな短いスワイプも無視
        longSwipes: false,         // ← 長いスワイプも無視
        slideToClickedSlide: false,// ← クリックでそのスライドへ移動しない

        effect: 'slide',
        
        centeredSlides: true,            // 常に中央揃え
        centerInsufficientSlides: true,  // 枚数が少なくても中央に

        speed: 9000,
        loop: true,
        slidesPerView: 2,
        spaceBetween: 40,

        // ← 慣性を完全に切る
        freeMode: { 
            enabled: true, 
            momentum: false, 
            sticky: false,
            momentumBounce: false
        },
        resistance: false,       // 端のゴム感を無効
        resistanceRatio: 0,
        autoplay: {
            delay: 0,
            disableOnInteraction: false,  // ← クリック・ドラッグでも止まらない
            pauseOnMouseEnter: false,     // ← ホバーでも止まらない
        },

        breakpoints: {
            999: {
                slidesPerView: 3,
                spaceBetween: 60,
            }, 
            767: {
                slidesPerView: 3,
                spaceBetween: 40,
            },

            599: {
                slidesPerView: 3,
                spaceBetween: 20,
            }
        }
    });
    return { swiperUpper, swiperLower };
}

