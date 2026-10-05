<?php

// Filipino validation messages for the app's forms. Rules not listed here fall back to English.
return [
    'array' => 'Dapat na listahan ang :attribute.',
    'between' => ['numeric' => 'Ang :attribute ay dapat nasa pagitan ng :min at :max.'],
    'boolean' => 'Dapat na oo o hindi ang :attribute.',
    'confirmed' => 'Hindi tugma ang kumpirmasyon ng :attribute.',
    'email' => 'Dapat na wastong email address ang :attribute.',
    'in' => 'Hindi wasto ang napiling :attribute.',
    'integer' => 'Dapat na buong numero ang :attribute.',
    'max' => ['numeric' => 'Ang :attribute ay hindi dapat lumampas sa :max.', 'string' => 'Ang :attribute ay hindi dapat lumampas sa :max na karakter.'],
    'min' => ['numeric' => 'Ang :attribute ay dapat hindi bababa sa :min.', 'string' => 'Ang :attribute ay dapat may hindi bababa sa :min na karakter.'],
    'required' => 'Kailangan ang :attribute.',
    'size' => ['array' => 'Ang :attribute ay dapat may :size na sagot.'],
    'string' => 'Dapat na teksto ang :attribute.',
    'unique' => 'Ginagamit na ang :attribute na ito.',
    'attributes' => [
        'name' => 'pangalan', 'email' => 'email', 'phone' => 'numero ng telepono', 'password' => 'password',
        'age' => 'edad', 'role' => 'uri ng user', 'answers' => 'mga sagot',
    ],
];
