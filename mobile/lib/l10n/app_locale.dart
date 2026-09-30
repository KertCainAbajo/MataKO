import 'package:flutter/material.dart';

/// App-wide locale state, updated when a language is selected during setup.
abstract final class AppLocale {
  static final current = ValueNotifier<Locale>(const Locale('en'));

  static Locale fromLanguageName(String language) => switch (language) {
        'Filipino' => const Locale('fil'),
        'Cebuano' => const Locale('ceb'),
        _ => const Locale('en'),
      };

  static String languageName(Locale locale) => switch (locale.languageCode) {
        'fil' => 'Filipino',
        'ceb' => 'Cebuano',
        _ => 'English',
      };

  static void setLanguage(String language) {
    current.value = fromLanguageName(language);
  }
}
