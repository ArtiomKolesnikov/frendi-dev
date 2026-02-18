(function () {
    // Wait for Swiper to be available
    const waitForSwiper = () => {
        return new Promise((resolve) => {
            if (typeof Swiper !== 'undefined') {
                resolve();
                return;
            }
            const check = setInterval(() => {
                if (typeof Swiper !== 'undefined') {
                    clearInterval(check);
                    resolve();
                }
            }, 50);
        });
    };

    waitForSwiper().then(() => {
        init();
    });

    function init() {
        const upload = document.getElementById('photoUpload');
        const fileInput = document.getElementById('fileInput');
        const typeSelect = document.getElementById('type');
        const contestField = document.querySelector('.contest-field');
        const closeIconPath = upload?.dataset?.closeIcon || '/images/close.svg';

        const avatarUpload = document.getElementById('avatarUpload');
        const avatarInput = document.getElementById('avatarInput');

        const mainSwiperEl = document.querySelector('[data-media-swiper="main"]');
        const mainWrapper = mainSwiperEl ? mainSwiperEl.querySelector('.swiper-wrapper') : null;

        const initSwiper = (container) => {
            if (!container) {
                return null;
            }

            return new Swiper(container, {
                slidesPerView: 'auto',
                spaceBetween: 12,
                freeMode: true,
                scrollbar: {
                    el: container.querySelector('.swiper-scrollbar'),
                    draggable: true,
                },
                breakpoints: {
                    768: {
                        spaceBetween: 16,
                    },
                },
            });
        };

        let mainSwiper = initSwiper(mainSwiperEl);

        // Init swiper for existing media on edit page
        const existingSwiperEl = document.querySelector('[data-media-swiper="existing"]');
        let existingSwiper = existingSwiperEl ? initSwiper(existingSwiperEl) : null;
    let pendingFiles = [];

    const updateFileInput = () => {
        if (!fileInput || typeof DataTransfer === 'undefined') {
            return;
        }

        const dataTransfer = new DataTransfer();
        pendingFiles.forEach((file) => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;
    };

    const formEl = document.querySelector('.new-post__form');
    if (formEl) {
        formEl.addEventListener('submit', () => {
            updateFileInput();
        });
    }

    const bindExistingPreview = (checkbox) => {
        const preview = document.querySelector(`[data-media-preview="${checkbox.value}"]`);
        if (!preview) {
            return;
        }

        const removeButton = preview.querySelector('[data-toggle-remove]');

        const toggleState = () => {
            preview.classList.toggle('is-marked', checkbox.checked);
        };

        if (removeButton) {
            removeButton.addEventListener('click', (event) => {
                event.preventDefault();
                checkbox.checked = !checkbox.checked;
                checkbox.dispatchEvent(new Event('change'));
            });
        }

        preview.addEventListener('click', (event) => {
            if (event.target.closest('[data-toggle-remove]')) {
                return;
            }
            checkbox.checked = !checkbox.checked;
            checkbox.dispatchEvent(new Event('change'));
        });

        checkbox.addEventListener('change', toggleState);
        toggleState();
    };

    document.querySelectorAll('[data-remove-media]').forEach(bindExistingPreview);

    const toggleContestField = () => {
        if (!typeSelect || !contestField) {
            return;
        }
        contestField.hidden = typeSelect.value !== 'contest';
    };

    if (typeSelect && contestField) {
        typeSelect.addEventListener('change', toggleContestField);
        toggleContestField();
    }

    // Rely on the native file input overlay; avoid JS-triggered clicks to prevent double dialogs

    const clearNewSlides = () => {
        if (!mainWrapper) {
            return;
        }
        mainWrapper.querySelectorAll('.swiper-slide[data-new-index]').forEach((slide) => slide.remove());
    };

    const bindNewPreviewRemove = () => {
        if (!mainWrapper) {
            return;
        }

        mainWrapper.querySelectorAll('[data-remove-new]').forEach((removeIcon) => {
            removeIcon.addEventListener('click', (event) => {
                event.preventDefault();
                const slide = removeIcon.closest('.swiper-slide');
                const index = Number(removeIcon.dataset.removeNew);
                if (!Number.isInteger(index)) {
                    return;
                }
                pendingFiles.splice(index, 1);
                if (slide) {
                    slide.remove();
                }
                renderPendingSlides();
            });
        });
    };

    const renderPendingSlides = () => {
        if (!mainWrapper) {
            return;
        }

        clearNewSlides();

        if (!pendingFiles.length) {
            updateFileInput();
            if (mainSwiper) {
                mainSwiper.update();
            }
            return;
        }

        pendingFiles.forEach((file, index) => {
            const slide = document.createElement('div');
            slide.className = 'swiper-slide';
            slide.dataset.newIndex = String(index);
            slide.innerHTML = `
                <div class="media-preview media-preview--new" data-new-index="${index}">
                    <img class="new-post__card_close" src="${closeIconPath}" alt="Удалить" data-remove-new="${index}">
                    <img class="new-post__card_img" alt="preview">
                </div>
            `;

            const image = slide.querySelector('.new-post__card_img');
            const reader = new FileReader();
            reader.onload = (event) => {
                if (image) {
                    image.src = event.target?.result || '';
                }
            };
            reader.readAsDataURL(file);

            mainWrapper.appendChild(slide);
        });

        updateFileInput();

        if (mainSwiper) {
            mainSwiper.update();
        } else if (mainSwiperEl) {
            mainSwiper = initSwiper(mainSwiperEl);
        }

        bindNewPreviewRemove();
    };

    if (fileInput) {
        fileInput.addEventListener('change', () => {
            const selected = Array.from(fileInput.files || []).filter((file) => file.type.startsWith('image/'));
            if (!selected.length) {
                return;
            }

            pendingFiles.push(...selected);
            renderPendingSlides();
        });
    }

    // Avatar upload handling
    if (avatarInput && avatarUpload) {
        const closeIcon = avatarUpload.dataset.closeIcon || '/images/close.svg';
        const ensurePreviewNodes = (src) => {
            let img = avatarUpload.querySelector('[data-avatar-preview]');
            if (!img) {
                img = document.createElement('img');
                img.className = 'new-post__card_img';
                img.style.objectFit = 'cover';
                img.setAttribute('alt', 'avatar');
                img.setAttribute('data-avatar-preview', '');
                avatarUpload.appendChild(img);
            }
            img.src = src;

            let removeBtn = avatarUpload.querySelector('[data-remove-avatar]');
            if (!removeBtn) {
                removeBtn = document.createElement('img');
                removeBtn.className = 'new-post__card_close';
                removeBtn.setAttribute('alt', 'Удалить');
                removeBtn.setAttribute('src', closeIcon);
                removeBtn.setAttribute('data-remove-avatar', '');
                avatarUpload.appendChild(removeBtn);
                removeBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    // Remove preview nodes
                    avatarUpload.querySelector('[data-avatar-preview]')?.remove();
                    avatarUpload.querySelector('[data-remove-avatar]')?.remove();
                    // Replace input to clear selected file
                    const oldInput = avatarUpload.querySelector('#avatarInput');
                    if (oldInput) {
                        const fresh = document.createElement('input');
                        fresh.type = 'file';
                        fresh.id = 'avatarInput';
                        fresh.name = 'avatar';
                        fresh.accept = 'image/*';
                        oldInput.replaceWith(fresh);
                        fresh.addEventListener('change', onAvatarChange);
                    }
                });
            }
        };
        const onAvatarChange = () => {
            const inputEl = avatarUpload.querySelector('#avatarInput');
            const file = inputEl?.files?.[0];
            if (!file || !file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                ensurePreviewNodes(e.target?.result || '');
                if (inputEl) inputEl.style.display = 'none';
            };
            reader.readAsDataURL(file);
        };
        avatarInput.addEventListener('change', onAvatarChange);
        // If server rendered an existing preview, keep input hidden but intact
        if (avatarUpload.querySelector('[data-remove-avatar]') && avatarInput.files?.length === 0) {
            avatarInput.style.display = 'none';
        }
    }
    } // end init()
})();
