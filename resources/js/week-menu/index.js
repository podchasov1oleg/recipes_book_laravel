import TomSelect from "tom-select";

/**
 * Страница меню на неделю
 */
class WeekMenuIndexPage {
    constructor() {
        this.initRecipesSelects();
        this.bindAddRecipeEvents();
        this.bindButtonEvents();
    }

    /**
     * Инициализировать селекты рецептов
     */
    initRecipesSelects()
    {
        document.querySelectorAll('.js-recipes-select').forEach((el) => {
            const select = new TomSelect(el, {
                plugins: {
                    'checkbox_options': {
                        'checkedClassNames': ['ts-checked'],
                        'uncheckedClassNames': ['ts-unchecked'],
                    },
                },
            });

            select.on('dropdown_close', () => select.open());
        });
    }

    /**
     * Повесить события отображения селекта при нажатии на кнопку добавления рецепта
     */
    bindAddRecipeEvents()
    {
        document.querySelectorAll('.js-add-recipes-btn').forEach((el) => {
            el.addEventListener('click', event => {
                const btn = event.currentTarget;
                const popup = btn.parentElement.querySelector('.js-recipe-popup');
                const select = popup.querySelector('.js-recipes-select');

                // скрыть активные попапы
                document.querySelectorAll('.js-recipe-popup:not(.hidden)').forEach(activePopup => {
                    if (activePopup !== popup) {
                        activePopup.classList.add('hidden');
                    }
                });

                // отобразить попап
                popup.classList.toggle('hidden');
                // триггер работы tom-select
                select.tomselect.focus();
            });
        });
    }

    /**
     * Повесить события на кнопки
     */
    bindButtonEvents()
    {
        // кнопка закрытия селекта выбора рецептов
        document.querySelectorAll('.js-cancel-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                btn.closest('.js-recipe-popup').classList.add('hidden');
            });
        });
    }
}

new WeekMenuIndexPage();
