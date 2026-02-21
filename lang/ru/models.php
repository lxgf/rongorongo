<?php

return [
    'tablet' => [
        'label' => 'Табличка',
        'plural' => 'Таблички',
        'fields' => [
            'code' => 'Код Бартеля',
            'name' => 'Название',
            'location' => 'Местонахождение',
            'description' => 'Описание',
        ],
    ],
    'glyph' => [
        'label' => 'Знак',
        'plural' => 'Знаки',
        'fields' => [
            'barthel_code' => 'Код Бартеля',
            'description' => 'Описание',
            'image' => 'Изображение',
        ],
    ],
    'rendering' => [
        'label' => 'Начертание',
        'plural' => 'Начертания',
        'fields' => [
            'code' => 'Код начертания',
            'glyph' => 'Знак',
            'description' => 'Описание',
        ],
    ],
    'compound_glyph' => [
        'label' => 'Составной знак',
        'plural' => 'Составные знаки',
        'fields' => [
            'code' => 'Код лигатуры',
            'description' => 'Описание',
        ],
    ],
    'compound_glyph_part' => [
        'label' => 'Часть составного знака',
        'plural' => 'Части составных знаков',
        'fields' => [
            'compound_glyph' => 'Составной знак',
            'glyph' => 'Знак',
            'order' => 'Позиция',
        ],
    ],
    'tablet_line' => [
        'label' => 'Строка таблички',
        'plural' => 'Строки табличек',
        'fields' => [
            'tablet' => 'Табличка',
            'side' => 'Сторона',
            'line' => 'Номер строки',
            'direction' => 'Направление',
            'sides' => [
                'recto' => 'Лицевая (a)',
                'verso' => 'Оборотная (b)',
            ],
            'directions' => [
                'ltr' => 'Слева направо',
                'rtl' => 'Справа налево',
            ],
        ],
    ],
    'tablet_rendering' => [
        'label' => 'Вхождение знака',
        'plural' => 'Вхождения знаков',
        'fields' => [
            'tablet_line' => 'Строка таблички',
            'rendering' => 'Начертание',
            'compound_glyph' => 'Составной знак',
            'position' => 'Позиция',
            'is_inverted' => 'Перевёрнут (f)',
            'is_mirrored' => 'Зеркальный (b)',
            'is_small' => 'Уменьшен (s)',
            'is_enlarged' => 'Увеличен (V)',
            'is_truncated' => 'Усечён (t)',
            'is_distorted' => 'Деформирован (y)',
            'is_uncertain' => 'Сомнительный (?)',
            'is_nonstandard' => 'Нестандартный (x)',
        ],
    ],
    'image' => [
        'label' => 'Изображение',
        'plural' => 'Изображения',
        'fields' => [
            'path' => 'Файл',
            'type' => 'Тип',
            'imageable_type' => 'Тип сущности',
            'imageable_id' => 'ID сущности',
            'sort_order' => 'Порядок сортировки',
        ],
    ],
    'nav' => [
        'group' => 'Корпус',
    ],
];
