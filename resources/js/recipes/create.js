import TomSelect from "tom-select";

/**
 * Страница создания рецепта
 */
class RecipeCreatePage {
    constructor() {
        this.initProductSelect();
    }

    /**
     * Инициализировать мультиселект продуктов на базе Tom Select
     */
    initProductSelect() {
        const el = document.querySelector('#product-ids');

        if (!el) {
            return;
        }

        new TomSelect(el, {
            plugins: {
                remove_button: {
                    title: 'Удалить продукт',
                },
            },
        });
    }
}

new RecipeCreatePage();