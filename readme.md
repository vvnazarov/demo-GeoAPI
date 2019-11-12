## Geo API

Это демонстрационное приложение, реализующее простой REST API управления данными геообъектов.

#### Реализованы методы API:

**GET** /api/vv1

**GET** /api/vv1/{id}

**POST** /api/vv1

**PATCH** /api/vv1/{id}

**DELETE** /api/vv1/{id}

Свойства геообъекта (все обязательные):
- _name_ string:64
- _description_ string:256
- _type_ string {field | bed | mts}
- _geometry_ string, WKT Polygon

Для метода **DELETE** можно использовать дополнительный параметр
- _archive_ true

в этом случае геообъект будет не удалён, а "архивирован" (soft deleted)

#### Установка

1. _composer install_

2. Скопировать _.env.example_ -> _.env_
<br>Прописать данные сервера БД
<br>_php artisan key:generate_

3. Заполнить БД
<br>_php artisan migrate --seed_


#### Тесты / демо

Коллекция для Postman

https://github.com/vvnazarov/geo/blob/master/tests/geo.postman_collection.json


## Release 1.3.0 notes

Получил обратную связь о том, что хорошо бы вынести логику из контроллера на Service Layer.
<br>На самом деле, с учётом того, что это - микро~~сервис~~задача, вариант не бесспорный.
<br>_Хорошо_: контроллер стал совсем тонкий
<br>_Плохо_: добавилась новая сущность, не являющаяся необходимой **для конкретной задачи**.
<br>На мой вкус, _плюс_ немного весомее _минуса_, поэтому этот релиз появился.

