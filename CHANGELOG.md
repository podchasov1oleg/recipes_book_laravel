# Changelog

Формат основан на [Keep a Changelog](https://keepachangelog.com/ru/1.0.0/).
Версионирование — см. раздел «Версионирование и changelog» в `CLAUDE.md`
(до `1.0.0` вместо major используется minor: `0.x.0` — breaking change,
`0.x.y` — обратно совместимый фикс/фича).

## Unreleased

### Добавлено

- CRUD продуктов: контроллер, модель, миграция, форм-реквесты
  со валидацией (включая проверку уникальности названия).
- Blade-шаблоны списка, создания и редактирования продукта.
- Фича-тесты на CRUD продуктов (`tests/Feature/ProductTest.php`).
- Сидер тестовых продуктов (`ProductSeeder`).
- Русский перевод стандартных сообщений валидации Laravel (`lang/ru`).
- CRUD рецептов: контроллер, модель, миграции (включая pivot-таблицу
  `product_recipe` с `cascadeOnDelete`), форм-реквесты с валидацией
  `product_ids` (`exists`, `min:1`), политика `RecipePolicy`.
- Blade-шаблоны списка, создания и редактирования рецепта; пункт
  «Рецепты» в навигации.
- Мультиселект продуктов для формы рецепта на базе Tom Select
  (отдельная точка входа в Vite, стили в `resources/css/tom-select.css`).
- Склонение количества ингредиентов ("N ингредиент/ингредиента/
  ингредиентов") через `trans_choice` и `lang/ru/recipes.php`.
- Сидер тестовых рецептов (`RecipeSeeder`).
- Фича-тесты на CRUD рецептов (`tests/Feature/RecipeTest.php`).
- Меню на неделю: контроллер `WeekMenuController`, модель `MenuDay`,
  миграции таблиц `menu_days` и pivot `menu_day_recipe`, форм-реквест
  `StoreMenuDayRequest`.
- Blade-шаблон недельной сетки по дням с выбором рецептов через
  Tom Select (плагин `checkbox_options`), отдельная точка входа
  в Vite (JS и CSS) для страницы меню.
- Добавление и удаление рецептов для конкретного дня недели
  (маршруты `week-menu.store`/`week-menu.destroy`).
- Пункт «Меню на неделю» в навигации, ссылка ведёт на реализованную
  страницу.
- Сидер тестового меню на неделю (`MenuDaySeeder`).
- Фича-тесты на меню на неделю (`tests/Feature/WeekMenuTest.php`).