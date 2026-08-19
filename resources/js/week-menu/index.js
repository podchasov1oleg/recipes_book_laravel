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

        // кнопка получения списка продуктов
        const shoppingListButton = document.querySelector('.js-shopping-list-btn');
        const mondayDate = shoppingListButton.dataset.monday;
        shoppingListButton.addEventListener('click', () => {
            fetch(`/week-menu/shopping-list?monday=${mondayDate}`)
                .then(res => res.text())
                .then(html => {
                    const panel = document.querySelector('.js-shopping-list-panel');
                    panel.innerHTML = html;
                    this.togglePanel();

                    // кнопка сокрытия боковой панели
                    document.querySelector('.js-shopping-list-close')
                        .addEventListener('click', () => this.togglePanel());
                });
        });

        // клик по backdrop-у тоже убирает aside
        document.querySelector('.js-shopping-list-backdrop')
            .addEventListener('click', () => this.togglePanel());
    }

    /**
     * Переключить видимость боковой панели со списком продуктов
     */
    togglePanel()
    {
        document.querySelector('.js-shopping-list-panel').classList.toggle('translate-x-full');
        ['opacity-0', 'pointer-events-none'].forEach(className => {
            document.querySelector('.js-shopping-list-backdrop').classList.toggle(className);
        });
    }
}

new WeekMenuIndexPage();
