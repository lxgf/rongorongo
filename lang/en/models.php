<?php

return [
    'tablet' => [
        'label' => 'Tablet',
        'plural' => 'Tablets',
        'fields' => [
            'code' => 'Barthel code',
            'name' => 'Name',
            'location' => 'Location',
            'description' => 'Description',
        ],
    ],
    'glyph' => [
        'label' => 'Glyph',
        'plural' => 'Glyphs',
        'fields' => [
            'barthel_code' => 'Barthel code',
            'description' => 'Description',
            'image' => 'Image',
        ],
    ],
    'rendering' => [
        'label' => 'Rendering',
        'plural' => 'Renderings',
        'fields' => [
            'code' => 'Rendering code',
            'glyph' => 'Glyph',
            'description' => 'Description',
        ],
    ],
    'compound_glyph' => [
        'label' => 'Compound Glyph',
        'plural' => 'Compound Glyphs',
        'fields' => [
            'code' => 'Compound code',
            'description' => 'Description',
        ],
    ],
    'compound_glyph_part' => [
        'label' => 'Compound Glyph Part',
        'plural' => 'Compound Glyph Parts',
        'fields' => [
            'compound_glyph' => 'Compound Glyph',
            'glyph' => 'Glyph',
            'order' => 'Position',
        ],
    ],
    'tablet_line' => [
        'label' => 'Tablet Line',
        'plural' => 'Tablet Lines',
        'fields' => [
            'tablet' => 'Tablet',
            'side' => 'Side',
            'line' => 'Line number',
            'direction' => 'Direction',
            'sides' => [
                'recto' => 'Recto (a)',
                'verso' => 'Verso (b)',
            ],
            'directions' => [
                'ltr' => 'Left to Right',
                'rtl' => 'Right to Left',
            ],
        ],
    ],
    'tablet_rendering' => [
        'label' => 'Tablet Rendering',
        'plural' => 'Tablet Renderings',
        'fields' => [
            'tablet_line' => 'Tablet Line',
            'rendering' => 'Rendering',
            'compound_glyph' => 'Compound Glyph',
            'position' => 'Position',
            'is_inverted' => 'Inverted (f)',
            'is_mirrored' => 'Mirrored (b)',
            'is_small' => 'Small (s)',
            'is_enlarged' => 'Enlarged (V)',
            'is_truncated' => 'Truncated (t)',
            'is_distorted' => 'Distorted (y)',
            'is_uncertain' => 'Uncertain (?)',
            'is_nonstandard' => 'Nonstandard (x)',
        ],
    ],
    'image' => [
        'label' => 'Image',
        'plural' => 'Images',
        'fields' => [
            'path' => 'File',
            'type' => 'Type',
            'imageable_type' => 'Entity type',
            'imageable_id' => 'Entity ID',
            'sort_order' => 'Sort order',
        ],
    ],
    'nav' => [
        'group' => 'Corpus',
    ],
];
