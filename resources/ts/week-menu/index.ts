import TomSelect from "tom-select";

type TomSelectInput = HTMLElement & { tomselect?: TomSelect };

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
    private initRecipesSelects()
    {
        document.querySelectorAll<HTMLSelectElement>('.js-recipes-select').forEach((el) => {
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
    private bindAddRecipeEvents()
    {
        document.querySelectorAll<HTMLElement>('.js-add-recipes-btn').forEach((el) => {
            el.addEventListener('click', () => {
                const popup = el.parentElement?.querySelector('.js-recipe-popup');
                const select = popup?.querySelector<TomSelectInput>('.js-recipes-select');

                // скрыть активные попапы
                document.querySelectorAll('.js-recipe-popup:not(.hidden)').forEach(activePopup => {
                    if (activePopup !== popup) {
                        activePopup.classList.add('hidden');
                    }
                });

                // отобразить попап
                popup?.classList.toggle('hidden');
                // триггер работы tom-select
                select?.tomselect?.focus();
            });
        });
    }

    /**
     * Повесить события на кнопки
     */
    private bindButtonEvents()
    {
        // кнопка закрытия селекта выбора рецептов
        document.querySelectorAll('.js-cancel-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const popup = btn.closest('.js-recipe-popup');
                if (popup !== null) {
                    popup.classList.add('hidden');
                }
            });
        });

        // кнопка получения списка продуктов
        const shoppingListButton = document.querySelector<HTMLElement>('.js-shopping-list-btn');
        const panel = document.querySelector('.js-shopping-list-panel');
        const closeBtn = document.querySelector('.js-shopping-list-close');
        const backdrop = document.querySelector('.js-shopping-list-backdrop');

        if (shoppingListButton !== null) {
            const mondayDate: string | undefined = shoppingListButton.dataset.monday;
            shoppingListButton.addEventListener('click', () => {
                fetch(`/week-menu/shopping-list` + (mondayDate ? `?monday=${mondayDate}` : ''))
                    .then(res => res.text())
                    .then(html => {
                        if (panel !== null) {
                            panel.innerHTML = html;
                        }
                        this.togglePanel();

                        // кнопка сокрытия боковой панели
                        if (closeBtn!==null) {
                            closeBtn.addEventListener('click', () => this.togglePanel());
                        }
                    });
            });
        }

        // клик по backdrop-у тоже убирает aside
        if (backdrop !== null) {
            backdrop.addEventListener('click', () => this.togglePanel());
        }
    }

    /**
     * Переключить видимость боковой панели со списком продуктов
     */
    private togglePanel()
    {
        const panel = document.querySelector('.js-shopping-list-panel');
        const backdrop = document.querySelector('.js-shopping-list-backdrop');

        if (panel !== null) {
            panel.classList.toggle('translate-x-full');
        }

        ['opacity-0', 'pointer-events-none'].forEach(className => {
            if (backdrop !== null) {
                backdrop.classList.toggle(className);
            }
        });
    }
}

new WeekMenuIndexPage();
