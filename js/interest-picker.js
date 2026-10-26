// 관심동물 선택(회원가입/프로필 편집 공통): 최대 개수 제한 + 기타 직접입력 토글
(function () {
    const MAX = 3;
    const picker = document.querySelector('.interest-picker');
    if (!picker) return;

    const checkboxes = Array.from(picker.querySelectorAll('input[type="checkbox"]'));
    const otherInput = picker.querySelector('.interest-other-input');
    const otherCheckbox = checkboxes.find(cb => cb.value === '기타');

    function update() {
        const checkedCount = checkboxes.filter(cb => cb.checked).length;
        checkboxes.forEach(cb => {
            cb.disabled = !cb.checked && checkedCount >= MAX;
        });

        if (otherInput && otherCheckbox) {
            if (otherCheckbox.checked) {
                otherInput.style.display = '';
            } else {
                otherInput.style.display = 'none';
                otherInput.value = '';
            }
        }
    }

    checkboxes.forEach(cb => cb.addEventListener('change', update));
    update();
})();
