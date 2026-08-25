import TomSelect from "tom-select";
import Alpine, {AlpineComponent} from "alpinejs";

/**
 * Объект продукта
 */
type TProduct = {
    product_id: number,
    unit: string,
    quantity: number,
    title: string,
};

/**
 * Состояние компонента создания рецепта
 */
type TRecipeCreateComponent = {
    products: TProduct[],
    quantityErrors: Record<number, string>,
    tomSelectInstance: TomSelect | null,
    removeProduct: (index: number) => void,
}

/**
 * Страница создания рецепта
 */
class RecipeCreatePage
{

    /**
     * Массив выбранных продуктов
     * @type {{product_id: number, unit: string, quantity: number, title: string}[]}
     */
    public products: TProduct[] = [];

    constructor()
    {
        this.initAlpine();
    }

    private initAlpine()
    {
        document.addEventListener('alpine:init', () => {
            Alpine.data('recipeForm', this.recipeFormFactory);
        });
    }

    /**
     * Фабрика состояния для компонента создания рецепта
     * @private
     */
    private recipeFormFactory(
        initialProducts: TProduct[] = [],
        quantityErrors: Record<number, string> = {}
    ): AlpineComponent<TRecipeCreateComponent>
    {
        return {
            products: initialProducts,
            tomSelectInstance: null as TomSelect | null,
            quantityErrors,

            init()
            {
                // выносим в отдельную переменную, чтобы компилятор не выдавал ошибку типа
                const instance = new TomSelect(
                    this.$refs.productSelect as HTMLSelectElement,
                    {
                        plugins: {
                            remove_button: {title: 'Удалить продукт'},
                        },
                    }
                );

                this.tomSelectInstance = instance;

                // убрать из выпадающего списка уже имеющиеся продукты
                this.products.forEach((product) => instance.removeOption(String(product.product_id)));

                instance.on('item_add', (value: string) => {
                    const option = instance.options[value];
                    this.products.push({
                        product_id: Number(value),
                        unit: option.unit,
                        title: option.title,
                        quantity: 0,
                    });

                    instance.clear(true);
                    instance.removeOption(value);
                });
            },

            removeProduct(index: number)
            {
                const [option] = this.products.splice(index, 1);

                this?.tomSelectInstance?.addOption({
                    value: String(option.product_id),
                    text: option.title,
                    unit: option.unit,
                    title: option.title,
                });
                this?.tomSelectInstance?.refreshOptions(false);
            }
        };
    }
}

new RecipeCreatePage();
