<?php

// Cebuano validation messages for the app's forms. Rules not listed here fall back to English.
return [
    'array' => 'Kinahanglan nga listahan ang :attribute.',
    'between' => ['numeric' => 'Ang :attribute kinahanglan tali sa :min ug :max.'],
    'boolean' => 'Kinahanglan oo o dili ang :attribute.',
    'confirmed' => 'Dili pareho ang kumpirmasyon sa :attribute.',
    'email' => 'Kinahanglan husto nga email address ang :attribute.',
    'in' => 'Dili husto ang gipili nga :attribute.',
    'integer' => 'Kinahanglan tibuok nga numero ang :attribute.',
    'max' => ['numeric' => 'Ang :attribute dili molapas sa :max.', 'string' => 'Ang :attribute dili molapas sa :max ka karakter.'],
    'min' => ['numeric' => 'Ang :attribute kinahanglan dili moubos sa :min.', 'string' => 'Ang :attribute kinahanglan adunay labing menos :min ka karakter.'],
    'required' => 'Kinahanglan ang :attribute.',
    'size' => ['array' => 'Ang :attribute kinahanglan adunay :size ka tubag.'],
    'string' => 'Kinahanglan teksto ang :attribute.',
    'unique' => 'Gigamit na kini nga :attribute.',
    'attributes' => [
        'name' => 'ngalan', 'email' => 'email', 'phone' => 'numero sa telepono', 'password' => 'password',
        'age' => 'edad', 'role' => 'klase sa user', 'answers' => 'mga tubag',
    ],
];
