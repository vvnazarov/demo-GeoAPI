## Geo API

Это демонстрационное приложение, реализующее простой REST API геообъектов.

### Реализованы методы API:

**GET** /api/vv1

**GET** /api/vv1/{id}

**POST** /api/vv1

**PATCH** /api/vv1/{id}

**DELETE** /api/vv1/{id}

Свойства геообъекта (все обязательные):
- name string:64
- description string:256
- type string {field | bed | mts}
- geometry string, WKT Polygon

Для метода **DELETE** пожно использовать дополнительный параметр
- archive true

в этом случае геообъект будет не удалён, а "архивирован" (soft deleted)
