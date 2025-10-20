import { useEffect, useState } from "react";

const makePairs = (arr) => {
  const out = [];
  for (let i = 0; i < arr.length; i += 2) out.push(arr.slice(i, i + 2));
  return out;
};

export default function UpperSlider({ images = [] }) {
  // 初回は決定的（非ランダム・2枚組）→ SSRと一致
  const [pairs, setPairs] = useState(() => makePairs(images));

  useEffect(() => {
    const recompute = () => {
      const shuffled = images.slice().sort(() => Math.random() - 0.5); // 乱数はクライアントのみ
      const isMobile = window.innerWidth <= 999;
      setPairs(isMobile ? shuffled.map((img) => [img]) : makePairs(shuffled));
    };
    recompute();
    window.addEventListener("resize", recompute);
    return () => window.removeEventListener("resize", recompute);
  }, [images]);

  return (
    <div className="upperSwiper">
      <div className="swiper-wrapper">
        {pairs.map((pair, i) => (
          <div className="swiper-slide group" key={i}>
            <div className="photo">
              <img src={pair[0]} alt={`slide ${i * 2 + 1}`} loading="lazy" />
            </div>
            {pair[1] && (
              <div className="photo">
                <img src={pair[1]} alt={`slide ${i * 2 + 2}`} loading="lazy" />
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
  );
}
