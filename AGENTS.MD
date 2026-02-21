# Rongorongo Research Platform

Платформа для исследования письма ронгоронго (остров Пасхи).
Цель — структурировать корпус глифов, визуализировать связи и создать инструменты для попытки расшифровки.

## Стек

- **Backend**: Laravel 12 (PHP 8.4)
- **Admin**: Filament v5
- **Database**: SQLite
- **Frontend**: TBD
- **Локализация**: EN / RU (переключатель в админке)

---

## Структура проекта

```
app/
├── Models/
│   ├── Tablet.php
│   ├── Glyph.php
│   ├── Rendering.php
│   ├── CompoundGlyph.php
│   ├── CompoundGlyphPart.php
│   ├── TabletLine.php
│   └── TabletRendering.php
├── Filament/
│   └── Resources/
│       ├── TabletResource.php          (+Pages, +RelationManagers/LinesRelationManager)
│       ├── GlyphResource.php           (+Pages, +RelationManagers/RenderingsRelationManager)
│       ├── CompoundGlyphResource.php   (+Pages, +RelationManagers/PartsRelationManager)
│       ├── RenderingResource.php       (+Pages)
│       ├── TabletLineResource.php      (+Pages)
│       ├── TabletRenderingResource.php (+Pages)
│       └── CompoundGlyphPartResource.php (+Pages)
├── Http/Middleware/
│   └── SetLocale.php
└── Providers/Filament/
    └── AdminPanelProvider.php

lang/
├── en/models.php
└── ru/models.php

database/migrations/
├── 2024_01_01_000001_create_tablets_table.php
├── 2024_01_01_000002_create_glyphs_table.php
├── 2024_01_01_000003_create_renderings_table.php
├── 2024_01_01_000004_create_compound_glyphs_table.php
├── 2024_01_01_000005_create_compound_glyph_parts_table.php
├── 2024_01_01_000006_create_tablet_lines_table.php
└── 2024_01_01_000007_create_tablet_renderings_table.php
```

---

## Структура базы данных

### `tablets` — таблички

| Колонка | Тип | Описание |
|---|---|---|
| `id` | bigint PK | |
| `code` | varchar(5) | Буква по Бартелю: A, B, C... |
| `name` | varchar(100) | Название: Mamari, Santiago Staff... |
| `location` | varchar(200) | Где хранится сейчас |
| `description` | text | Описание |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

---

### `glyphs` — знаки (абстрактные, ~120 штук)

| Колонка | Тип | Описание |
|---|---|---|
| `id` | bigint PK | |
| `barthel_code` | varchar(10) UNIQUE | Номер по Бартелю: 001, 022, 600 |
| `description` | text | Что известно о знаке |
| `image` | varchar(255) | Путь к изображению |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

---

### `renderings` — начертания (конкретные варианты знака)

Подварианты формы одного знака: 022a, 022c, 022d...
Модификаторы-подварианты из нотации Бартеля: a, c, d, j, h, e, k, i, o, g

| Колонка | Тип | Описание |
|---|---|---|
| `id` | bigint PK | |
| `glyph_id` | bigint FK | → glyphs |
| `code` | varchar(10) UNIQUE | Полный код: 022a, 022c... |
| `description` | text | |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

---

### `compound_glyphs` — составные знаки (лигатуры)

Два и более знака нарисованы слитно: 001.006, 001.006.022...
В корпусе встречаются лигатуры из 2, 3, 4 и 5 частей.

| Колонка | Тип | Описание |
|---|---|---|
| `id` | bigint PK | |
| `code` | varchar(30) UNIQUE | Полный код: 001.006.022 |
| `description` | text | |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### `compound_glyph_parts` — из чего состоит составной знак

| Колонка | Тип | Описание |
|---|---|---|
| `id` | bigint PK | |
| `compound_glyph_id` | bigint FK | → compound_glyphs |
| `glyph_id` | bigint FK | → glyphs |
| `order` | tinyint unsigned | Порядок части (1, 2, 3...) |

**Индексы:**
```sql
UNIQUE (compound_glyph_id, order)
INDEX  (glyph_id)
```

---

### `tablet_lines` — строки табличек

Бустрофедон: табличка читается змейкой с переворотом.
Направление каждой строки хранится явно — вычислять нельзя, у разных табличек разная разметка.

| Колонка | Тип | Описание |
|---|---|---|
| `id` | bigint PK | |
| `tablet_id` | bigint FK | → tablets |
| `side` | tinyint(1) | 0 = лицо (recto), 1 = обратная (verso) |
| `line` | tinyint unsigned | Номер строки на стороне (1, 2, 3...) |
| `direction` | enum('ltr','rtl') | Направление чтения |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

**Индексы:**
```sql
UNIQUE (tablet_id, side, line)
```

---

### `tablet_renderings` — вхождения знаков в таблички

На каждой позиции стоит либо начертание либо составной знак — одно из двух, второе NULL.

| Колонка | Тип | Описание |
|---|---|---|
| `id` | bigint PK | |
| `tablet_line_id` | bigint FK | → tablet_lines |
| `rendering_id` | bigint FK nullable | → renderings |
| `compound_glyph_id` | bigint FK nullable | → compound_glyphs |
| `position` | smallint unsigned | Позиция в строке (1, 2, 3...) |
| `is_inverted` | bool | Перевёрнут вертикально (f) |
| `is_mirrored` | bool | Зеркальный (b) |
| `is_small` | bool | Уменьшен (s) |
| `is_enlarged` | bool | Увеличен (V) |
| `is_truncated` | bool | Усечён (t) |
| `is_distorted` | bool | Деформирован (y) |
| `is_uncertain` | bool | Сомнительное чтение (?) |
| `is_nonstandard` | bool | Нестандартная форма (x) |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

**Индексы:**
```sql
UNIQUE (tablet_line_id, position)
INDEX  (rendering_id)
INDEX  (compound_glyph_id)
```

---

## Связи (Eloquent)

```
Tablet
  └── hasMany TabletLine
        └── hasMany TabletRendering
              ├── belongsTo Rendering
              │     └── belongsTo Glyph
              └── belongsTo CompoundGlyph
                    └── hasMany CompoundGlyphPart
                          └── belongsTo Glyph

Glyph
  └── hasMany Rendering
  └── hasMany CompoundGlyphPart
```

Чтение строки в порядке:
```php
TabletLine::where('tablet_id', $id)
    ->where('side', 0)
    ->where('line', 1)
    ->first()
    ->tabletRenderings()
    ->orderBy('position')
    ->with(['rendering.glyph', 'compoundGlyph.parts.glyph'])
    ->get();
```

Все вхождения знака во всех табличках:
```php
Glyph::find($id)
    ->renderings()
    ->with('tabletRenderings.tabletLine.tablet')
    ->get();
```

---

## Filament Admin

**URL:** `/admin`
**Учётка:** `admin@rongorongo.local` / `password`

### Ресурсы (7 шт)

Все ресурсы в группе навигации «Corpus» / «Корпус»:

| Ресурс | Модель | RelationManagers |
|---|---|---|
| TabletResource | Tablet | LinesRelationManager |
| GlyphResource | Glyph | RenderingsRelationManager |
| CompoundGlyphResource | CompoundGlyph | PartsRelationManager |
| RenderingResource | Rendering | — |
| TabletLineResource | TabletLine | — |
| TabletRenderingResource | TabletRendering | — |
| CompoundGlyphPartResource | CompoundGlyphPart | — |

### Локализация

- Переключатель EN/RU в topbar (флаг-кнопка рядом с user menu)
- Locale хранится в сессии, применяется через `SetLocale` middleware
- Переводы Filament UI — из коробки (`vendor/filament/*/lang/ru/`)
- Переводы лейблов моделей и полей — `lang/ru/models.php`, `lang/en/models.php`
- Роут переключения: `GET /locale/{locale}` → `locale.switch`

---

## Данные

Источник корпуса — репозиторий [rongopy](https://github.com/jgregoriods/rongopy).

Файл `ga_lstm/tablets/tablets_clean.json` — оцифрованные тексты 13 табличек
в числовых кодах Бартеля, формат:

```json
{
  "A": {
    "Aa1": "430-040-320-009-...",
    "Aa2": "201-027-103-..."
  }
}
```

Ключ строки парсится: `Aa1` → табличка=A, сторона=a(0), строка=1.

**Статистика корпуса:**
- 13 табличек
- ~568 уникальных кодов глифов
- ~11 666 вхождений
- Лигатуры: 2166 двойных, 315 тройных, 25 четверных, 4 пятерных

---

## Решения

- **Импорт** — Filament action (кнопка в админке)
- **Модификатор `x`** — добавлен флаг `is_nonstandard` в `tablet_renderings`

---

## Следующие шаги

- [x] Создать Laravel-проект
- [x] Написать миграции
- [x] Создать модели с relationship
- [x] Подключить Filament
- [x] Настроить ресурсы Filament (все 7 таблиц)
- [x] Локализация EN/RU с переключателем
- [ ] Написать импорт из `tablets_clean.json` (Filament action)

---

## Открытые вопросы

- **Параллельные места** — одинаковые последовательности знаков на разных табличках. Отдельная сущность для разметки или искать запросами по факту?

---

## Технические заметки (Filament v5 + PHP 8.4)

- `form()` принимает `Filament\Schemas\Schema`, не `Filament\Forms\Form`
- Используем `->components([...])` вместо `->schema([...])`
- Все actions (`EditAction`, `ViewAction`, `DeleteAction`, `CreateAction`, `BulkActionGroup`, `DeleteBulkAction`) в `Filament\Actions`, НЕ `Filament\Tables\Actions`
- Свойства с union types должны точно совпадать с родительским классом:
  - `$navigationIcon`: `string|\BackedEnum|null`
  - `$navigationGroup`: `string|\UnitEnum|null`
- `Tables\Columns\*` — namespace колонок не изменился
- После установки обязательно: `php artisan filament:assets`
- ext-intl должен быть включён в php.ini
