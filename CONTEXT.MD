# Контекст проекта: Rongorongo Research Platform

## Что это

Веб-платформа для исследования письма ронгоронго (остров Пасхи, нерасшифрованная письменность).
Цель — структурировать корпус знаков и создать инструменты для анализа и попытки расшифровки.

---

## Стек

- **Backend**: Laravel 12 (PHP 8.4)
- **Admin**: Filament v5
- **Database**: SQLite
- **Frontend**: TBD
- **Локализация**: EN / RU (переключатель в админке)

---

## Что уже сделано

- [x] Laravel-проект создан
- [x] Миграции написаны (7 таблиц)
- [x] Модели с relationships
- [x] Filament подключён
- [x] 7 ресурсов Filament настроены
- [x] Локализация EN/RU с переключателем
- [ ] Импорт из `tablets.json` (следующий шаг)

---

## Структура базы данных

### `tablets` — таблички (13 штук: A, B, C, D, E, G, H, K, N, P, Q, R, S)

| Колонка | Тип | Описание |
|---|---|---|
| id | bigint PK | |
| code | varchar(5) | A, B, C... |
| name | varchar(100) | Mamari, Santiago Staff... |
| location | varchar(200) | Где хранится |
| description | text | |

---

### `glyphs` — абстрактные знаки (~120 базовых)

| Колонка | Тип | Описание |
|---|---|---|
| id | bigint PK | |
| barthel_code | varchar(10) UNIQUE | 001, 022, 600... |
| description | text | |
| image | varchar(255) | путь к GIF файлу: glyphs/022.GIF |

**Изображения**: 602 GIF файла из библиотеки Бартеля (kohaumotu.org).
Именование: `001.GIF`, `022.GIF`, `600.GIF` — имя файла = код знака.
Кладутся в `public/glyphs/`.

---

### `renderings` — начертания (варианты одного знака)

Буквы a, c, d, j, h, e, k, i, o, g в нотации Бартеля — это подварианты формы знака.
Например: 022a, 022c, 022d — три разных начертания знака 022.

| Колонка | Тип | Описание |
|---|---|---|
| id | bigint PK | |
| glyph_id | bigint FK | → glyphs |
| code | varchar(10) UNIQUE | 022a, 022c... |
| description | text | |

---

### `compound_glyphs` — составные знаки (лигатуры)

Два и более знака нарисованы слитно. В нотации через точку: `001.006`, `320.009`, `001.006.022`.
В корпусе: 2166 двойных, 315 тройных, 25 четверных, 4 пятерных лигатуры.

| Колонка | Тип | Описание |
|---|---|---|
| id | bigint PK | |
| code | varchar(30) UNIQUE | 001.006.022 |
| description | text | |

---

### `compound_glyph_parts` — из чего состоит составной знак

| Колонка | Тип | Описание |
|---|---|---|
| id | bigint PK | |
| compound_glyph_id | bigint FK | → compound_glyphs |
| glyph_id | bigint FK | → glyphs |
| order | tinyint unsigned | порядок: 1, 2, 3... |

Индексы: UNIQUE(compound_glyph_id, order), INDEX(glyph_id)

---

### `tablet_lines` — строки табличек

Бустрофедон: табличка читается змейкой с физическим переворотом.
Направление строки хранится явно — нельзя вычислить по формуле, у каждой таблички своя разметка.

| Колонка | Тип | Описание |
|---|---|---|
| id | bigint PK | |
| tablet_id | bigint FK | → tablets |
| side | tinyint(1) | 0 = recto (лицо), 1 = verso (обратная) |
| line | tinyint unsigned | номер строки на стороне (1, 2, 3...) |
| direction | enum('ltr','rtl') | направление чтения |

Индекс: UNIQUE(tablet_id, side, line)

---

### `tablet_renderings` — вхождения знаков в таблички

На каждой позиции стоит либо начертание (rendering_id) либо составной знак (compound_glyph_id).
Одно из двух всегда NULL.

| Колонка | Тип | Описание |
|---|---|---|
| id | bigint PK | |
| tablet_line_id | bigint FK | → tablet_lines |
| rendering_id | bigint FK nullable | → renderings |
| compound_glyph_id | bigint FK nullable | → compound_glyphs |
| position | smallint unsigned | позиция в строке |
| is_inverted | bool | перевёрнут (f) |
| is_mirrored | bool | зеркальный (b) |
| is_small | bool | уменьшен (s) |
| is_enlarged | bool | увеличен (V) |
| is_truncated | bool | усечён (t) |
| is_distorted | bool | деформирован (y) |
| is_uncertain | bool | сомнительное чтение (?) |
| is_nonstandard | bool | нестандартная форма (x) |

Индексы: UNIQUE(tablet_line_id, position), INDEX(rendering_id), INDEX(compound_glyph_id)

---

## Связи Eloquent

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

---

## Данные для импорта

### Источник корпуса

Репозиторий: https://github.com/jgregoriods/rongopy

Файл для импорта: `ga_lstm/tablets/tablets.json` — ПОЛНЫЕ данные с модификаторами и лигатурами.

**НЕ использовать** `tablets_clean.json` — там только числа без модификаторов, это упрощённая версия для ML.

### Формат tablets.json

```json
{
  "A": {
    "Aa1": "430!.040-320.009-022bf-008a.451V-...",
    "Aa2": "201-027-103a:095bjt-...",
    "Ab1": "..."
  },
  "C": { ... }
}
```

**Ключ строки парсится:**
- `Aa1` → табличка=A, сторона=a→0(recto), строка=1
- `Ab1` → табличка=A, сторона=b→1(verso), строка=1
- `Br3` → табличка=B, сторона=r→0(recto), строка=3
- `Bv3` → табличка=B, сторона=v→1(verso), строка=3

Варианты обозначения сторон: `a/b` или `r/v` в зависимости от таблички.

### Нотация токена

Токены разделены дефисом `-`. Каждый токен — одна позиция.

```
022bf        = знак 022, модификаторы b(зеркальный) + f(перевёрнут)
022bfy       = знак 022, b + f + y(деформирован)
008a         = знак 008, начертание a
008a.451V    = лигатура: 008a слито с 451, V=увеличен
320.009      = лигатура: 320 слито с 009
320.009.001  = тройная лигатура
005:008a     = знаки 005 и 008a стоят один над другим (стек, двоеточие)
430!         = знак 430, нестандартная/особая форма
195a?        = знак 195a, сомнительное чтение
000!         = неразборчивый знак
*            = конец строки (игнорировать)
```

### Буквенные суффиксы нотации Бартеля

**Геометрические модификаторы** (поля is_* в tablet_renderings):
- `f` = перевёрнут (is_inverted)
- `b` = зеркальный (is_mirrored)
- `s` = уменьшен (is_small)
- `V` = увеличен (is_enlarged)
- `t` = усечён (is_truncated)
- `y` = деформирован (is_distorted)
- `x` = нестандартная форма (is_nonstandard)

**Начертания** (подварианты формы, идут в renderings.code):
- `a`, `c`, `d`, `j`, `h`, `e`, `k`, `i`, `o`, `g`

**Спецсимволы:**
- `!` = особая/нестандартная форма
- `?` = сомнительное чтение (is_uncertain)
- `*` = конец строки
- `.` = лигатура (знаки слиты)
- `:` = стек (один над другим) — пока можно обрабатывать как лигатуру
- `'` = слияние (fused) — редко, аналогично лигатуре

### Статистика корпуса

- 13 табличек
- ~569 уникальных числовых кодов
- ~11 666 вхождений
- Лигатуры: 2166 двойных, 315 тройных, 25 четверных, 4 пятерных

---

## Filament Admin

**URL**: `/admin`
**Учётка**: `admin@rongorongo.local` / `password`

### Ресурсы (все в группе «Corpus»)

| Ресурс | Модель | RelationManagers |
|---|---|---|
| TabletResource | Tablet | LinesRelationManager |
| GlyphResource | Glyph | RenderingsRelationManager |
| CompoundGlyphResource | CompoundGlyph | PartsRelationManager |
| RenderingResource | Rendering | — |
| TabletLineResource | TabletLine | — |
| TabletRenderingResource | TabletRendering | — |
| CompoundGlyphPartResource | CompoundGlyphPart | — |

### Технические заметки (Filament v5 + PHP 8.4)

- `form()` принимает `Filament\Schemas\Schema`, не `Filament\Forms\Form`
- Используем `->components([...])` вместо `->schema([...])`
- Все actions в `Filament\Actions`, НЕ `Filament\Tables\Actions`
- `$navigationIcon`: тип `string|\BackedEnum|null`
- `$navigationGroup`: тип `string|\UnitEnum|null`
- После установки пакетов: `php artisan filament:assets`
- Нужен ext-intl в php.ini

---

## Следующий шаг: импорт

Сделать Filament Action (кнопка в админке) которая:

1. Читает `tablets.json`
2. Для каждой таблички создаёт запись в `tablets`
3. Парсит ключ строки → создаёт `tablet_lines`
4. Парсит каждый токен строки:
   - Извлекает числовой код знака
   - Определяет начертание (буква-подвариант)
   - Определяет модификаторы (f, b, s, V, t, y, x)
   - Определяет лигатуру (точка между кодами)
   - Создаёт/находит `glyphs`, `renderings`, `compound_glyphs`
   - Создаёт `tablet_renderings` с позицией и флагами

---

## Открытые вопросы

- **Параллельные места** — одинаковые последовательности знаков на разных табличках. Нужна ли отдельная сущность или искать запросами?
- **Стек (двоеточие `:`)** — знаки один над другим. Пока нет поля в БД, обрабатывать как лигатуру или добавить отдельный флаг?
