(function () {

    const radios    = document.querySelectorAll('input[name="in_japan"]');
    const addrWrap  = document.getElementById('addressFields');
    const zipInput  = document.getElementById('zipcode');
    const prefInput = document.getElementById('prefecture');
    const cityInput = document.getElementById('city');
    const streetInp = document.getElementById('street');

    // Zip下の <small> をヘルプとして活用
    let help = zipInput?.nextElementSibling;
    const setHelp = (msg) => { if (help) help.textContent = msg; };

    const sanitizeZip = (v) => v.replace(/[^0-9]/g, '').slice(0, 8);
    const formatZip   = (digits) => (digits.length >= 4)
    ? `${digits.slice(0, 3)}-${digits.slice(3)}`
    : digits;

    function isInJapan() {
    return document.querySelector('input[name="in_japan"]:checked')?.value === 'yes';
    }

    function setAddressRequired(on) {
    [zipInput, prefInput, cityInput].forEach(el => el && (el.required = on));
    }

    function setAddressDisabled(on) {
    [zipInput, prefInput, cityInput, streetInp].forEach(el => el && (el.disabled = on));
    }

    function clearAddress() {
    [zipInput, prefInput, cityInput, streetInp].forEach(el => el && (el.value = ''));
    setHelp && setHelp('');
    }

    function toggleAddress() {
    const show = isInJapan();
    if (addrWrap) addrWrap.style.display = show ? '' : 'none';
    setAddressRequired(show);
    setAddressDisabled(!show);
    if (!show) clearAddress();
    }

    radios.forEach(r => r.addEventListener('change', toggleAddress));
    toggleAddress(); // 初期反映

    async function lookupZip(zip7) {
    try {
        const res  = await fetch(`https://zipcloud.ibsnet.co.jp/api/search?zipcode=${zip7}`);
        const data = await res.json();
        if (data.results && data.results[0]) {
        const r = data.results[0];
        if (prefInput) prefInput.value = r.address1 || '';
        if (cityInput) cityInput.value = (r.address2 || '') + (r.address3 || '');
        setHelp && setHelp('Address filled automatically.');
        } else {
        setHelp && setHelp('Postal code not found. Please fill address manually.');
        }
    } catch (err) {
        console.error('Zip lookup failed:', err);
        setHelp && setHelp('Lookup failed. Please fill address manually.');
    }
    }

    function maybeLookup() {
    if (!isInJapan() || !zipInput) return; // 非表示時/未初期化は呼ばない
    const digits = sanitizeZip(zipInput.value);
    if (digits.length === 7) lookupZip(digits);
    }

    // 入力中に整形（3-4桁に自動ハイフン）＋ 7桁で検索
    zipInput?.addEventListener('input', (e) => {
    if (!isInJapan()) return;
    const target = e.target;
    const digits = sanitizeZip(target.value);
    target.value = formatZip(digits);
    if (digits.length === 7) lookupZip(digits);
    });

    // フォーカスが外れた時にも検索
    zipInput?.addEventListener('blur', maybeLookup);

})();