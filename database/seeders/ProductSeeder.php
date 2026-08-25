<?php

namespace Database\Seeders;

use App\Enums\Unit;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Сидер таблицы продуктов
 */
class ProductSeeder extends Seeder
{
    /**
     * Продукты, которые могут быть частью рецепта
     */
    private const PRODUCTS = [
        // специи и приправы
        ['title' => 'орегано', 'unit' => Unit::Gram],
        ['title' => 'сушёный базилик', 'unit' => Unit::Gram],
        ['title' => 'мускатный орех', 'unit' => Unit::Gram],
        ['title' => 'розмарин', 'unit' => Unit::Gram],
        ['title' => 'тимьян', 'unit' => Unit::Gram],
        ['title' => 'куркума', 'unit' => Unit::Gram],
        ['title' => 'паприка сладкая', 'unit' => Unit::Gram],
        ['title' => 'перец чёрный молотый', 'unit' => Unit::Gram],
        ['title' => 'сушёный чеснок', 'unit' => Unit::Gram],
        ['title' => 'лепестки чили', 'unit' => Unit::Gram],
        ['title' => 'соль', 'unit' => Unit::Gram],
        ['title' => 'сахар', 'unit' => Unit::Gram],
        ['title' => 'сахарная пудра', 'unit' => Unit::Gram],
        ['title' => 'ванилин', 'unit' => Unit::Gram],
        ['title' => 'дрожжи сухие', 'unit' => Unit::Gram],
        ['title' => 'разрыхлитель', 'unit' => Unit::Gram],
        ['title' => 'сода', 'unit' => Unit::Gram],
        ['title' => 'лимонная кислота', 'unit' => Unit::Gram],
        ['title' => 'крахмал', 'unit' => Unit::Gram],
        ['title' => 'сухари панировочные', 'unit' => Unit::Gram],
        ['title' => 'кунжут', 'unit' => Unit::Gram],

        // масла, уксусы, соусы
        ['title' => 'масло подсолнечное рафинированное', 'unit' => Unit::Milliliter],
        ['title' => 'масло оливковое', 'unit' => Unit::Milliliter],
        ['title' => 'масло кунжутное', 'unit' => Unit::Milliliter],
        ['title' => 'уксус', 'unit' => Unit::Milliliter],
        ['title' => 'уксус винный', 'unit' => Unit::Milliliter],
        ['title' => 'соус соевый', 'unit' => Unit::Milliliter],
        ['title' => 'соус устричный', 'unit' => Unit::Milliliter],
        ['title' => 'соус терияки', 'unit' => Unit::Milliliter],
        ['title' => 'соус чили', 'unit' => Unit::Milliliter],
        ['title' => 'соус болоньезе', 'unit' => Unit::Milliliter],
        ['title' => 'соус цезарь', 'unit' => Unit::Milliliter],
        ['title' => 'соус барбекю', 'unit' => Unit::Milliliter],
        ['title' => 'кетчуп', 'unit' => Unit::Gram],
        ['title' => 'горчица', 'unit' => Unit::Gram],
        ['title' => 'майонез', 'unit' => Unit::Gram],

        // мука, крупы, макароны
        ['title' => 'мука', 'unit' => Unit::Gram],
        ['title' => 'гречка', 'unit' => Unit::Gram],
        ['title' => 'горох', 'unit' => Unit::Gram],
        ['title' => 'чечевица красная', 'unit' => Unit::Gram],
        ['title' => 'рис', 'unit' => Unit::Gram],
        ['title' => 'манка', 'unit' => Unit::Gram],
        ['title' => 'пшено', 'unit' => Unit::Gram],
        ['title' => 'геркулес', 'unit' => Unit::Gram],
        ['title' => 'макароны', 'unit' => Unit::Gram],
        ['title' => 'вермишель', 'unit' => Unit::Gram],

        // консервы и заготовки
        ['title' => 'кукуруза консервированная', 'unit' => Unit::Gram],
        ['title' => 'горошек консервированный', 'unit' => Unit::Gram],
        ['title' => 'фасоль красная', 'unit' => Unit::Gram],
        ['title' => 'помидоры в собственном соку', 'unit' => Unit::Gram],
        ['title' => 'огурцы маринованные', 'unit' => Unit::Gram],
        ['title' => 'маслины', 'unit' => Unit::Gram],
        ['title' => 'тунец консервированный', 'unit' => Unit::Gram],
        ['title' => 'паста томатная', 'unit' => Unit::Gram],
        ['title' => 'пюре томатов', 'unit' => Unit::Gram],

        // бульон
        ['title' => 'кубик бульонный куриный', 'unit' => Unit::Piece],
        ['title' => 'кубик бульонный говяжий', 'unit' => Unit::Piece],
        ['title' => 'кубик бульонный грибной', 'unit' => Unit::Piece],

        // хлеб и выпечка
        ['title' => 'хлеб белый', 'unit' => Unit::Gram],
        ['title' => 'хлеб чёрный', 'unit' => Unit::Gram],
        ['title' => 'лаваш', 'unit' => Unit::Piece],
        ['title' => 'булочки для бургеров', 'unit' => Unit::Piece],
        ['title' => 'тесто слоёное', 'unit' => Unit::Gram],

        // молочка и яйца
        ['title' => 'яйца', 'unit' => Unit::Piece],
        ['title' => 'молоко', 'unit' => Unit::Milliliter],
        ['title' => 'сливки', 'unit' => Unit::Milliliter],
        ['title' => 'сметана', 'unit' => Unit::Gram],
        ['title' => 'кефир', 'unit' => Unit::Milliliter],
        ['title' => 'йогурт', 'unit' => Unit::Gram],
        ['title' => 'творог', 'unit' => Unit::Gram],
        ['title' => 'сыр твёрдый', 'unit' => Unit::Gram],
        ['title' => 'сыр пармезан', 'unit' => Unit::Gram],
        ['title' => 'сыр моцарелла', 'unit' => Unit::Gram],
        ['title' => 'сыр фета', 'unit' => Unit::Gram],
        ['title' => 'сыр плавленый', 'unit' => Unit::Gram],
        ['title' => 'сыр творожный', 'unit' => Unit::Gram],
        ['title' => 'масло сливочное', 'unit' => Unit::Gram],

        // мясо, птица, рыба
        ['title' => 'куриное филе', 'unit' => Unit::Gram],
        ['title' => 'куриные бёдра', 'unit' => Unit::Gram],
        ['title' => 'курица целая', 'unit' => Unit::Piece],
        ['title' => 'фарш говяжий', 'unit' => Unit::Gram],
        ['title' => 'фарш куриный', 'unit' => Unit::Gram],
        ['title' => 'свинина вырезка', 'unit' => Unit::Gram],
        ['title' => 'свинина корейка', 'unit' => Unit::Gram],
        ['title' => 'говядина гуляш', 'unit' => Unit::Gram],
        ['title' => 'бекон', 'unit' => Unit::Gram],
        ['title' => 'сосиски', 'unit' => Unit::Gram],
        ['title' => 'колбаса варёная', 'unit' => Unit::Gram],
        ['title' => 'ветчина', 'unit' => Unit::Gram],
        ['title' => 'пельмени', 'unit' => Unit::Gram],
        ['title' => 'креветки очищенные', 'unit' => Unit::Gram],
        ['title' => 'филе минтая', 'unit' => Unit::Gram],
        ['title' => 'филе трески', 'unit' => Unit::Gram],
        ['title' => 'сёмга', 'unit' => Unit::Gram],
        ['title' => 'сельдь', 'unit' => Unit::Gram],

        // овощи
        ['title' => 'картофель', 'unit' => Unit::Gram],
        ['title' => 'лук репчатый', 'unit' => Unit::Gram],
        ['title' => 'чеснок', 'unit' => Unit::Gram],
        ['title' => 'морковь', 'unit' => Unit::Gram],
        ['title' => 'свёкла', 'unit' => Unit::Gram],
        ['title' => 'капуста белокочанная', 'unit' => Unit::Gram],
        ['title' => 'капуста цветная', 'unit' => Unit::Gram],
        ['title' => 'брокколи', 'unit' => Unit::Gram],
        ['title' => 'кабачок', 'unit' => Unit::Gram],
        ['title' => 'баклажан', 'unit' => Unit::Gram],
        ['title' => 'помидоры', 'unit' => Unit::Gram],
        ['title' => 'огурцы', 'unit' => Unit::Gram],
        ['title' => 'перец болгарский', 'unit' => Unit::Gram],
        ['title' => 'авокадо', 'unit' => Unit::Gram],
        ['title' => 'лимон', 'unit' => Unit::Gram],
        ['title' => 'петрушка', 'unit' => Unit::Gram],
        ['title' => 'укроп', 'unit' => Unit::Gram],
        ['title' => 'лук зелёный', 'unit' => Unit::Gram],
        ['title' => 'шпинат', 'unit' => Unit::Gram],
        ['title' => 'салат листовой', 'unit' => Unit::Gram],
        ['title' => 'руккола', 'unit' => Unit::Gram],
        ['title' => 'редис', 'unit' => Unit::Gram],
        ['title' => 'грибы шампиньоны', 'unit' => Unit::Gram],
        ['title' => 'имбирь', 'unit' => Unit::Gram],

        // фрукты
        ['title' => 'бананы', 'unit' => Unit::Gram],
        ['title' => 'яблоки', 'unit' => Unit::Gram],
        ['title' => 'груши', 'unit' => Unit::Gram],
        ['title' => 'апельсины', 'unit' => Unit::Gram],
        ['title' => 'мандарины', 'unit' => Unit::Gram],
        ['title' => 'киви', 'unit' => Unit::Gram],
        ['title' => 'персики', 'unit' => Unit::Gram],
        ['title' => 'абрикосы', 'unit' => Unit::Gram],
        ['title' => 'слива', 'unit' => Unit::Gram],
        ['title' => 'ананас', 'unit' => Unit::Gram],

        // орехи, сухофрукты, мёд
        ['title' => 'изюм', 'unit' => Unit::Gram],
        ['title' => 'орехи грецкие', 'unit' => Unit::Gram],
        ['title' => 'чернослив', 'unit' => Unit::Gram],
        ['title' => 'мёд', 'unit' => Unit::Gram],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::PRODUCTS as $product) {
            Product::create($product);
        }
    }
}
