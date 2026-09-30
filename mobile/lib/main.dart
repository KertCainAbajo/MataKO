import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';

import 'services/api_service.dart';
import 'screens/login_screen.dart';
import 'screens/splash_screen.dart';
import 'theme/app_colors.dart';
import 'l10n/app_locale.dart';
import 'l10n/app_localizations.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final api = ApiService();
  final hasAccount = await api.hasRegisteredAccount;
  final language = await api.preferredLanguage;
  AppLocale.setLanguage(language);
  runApp(MataKoApp(startAtLogin: hasAccount, initialLanguage: language));
}

class MataKoApp extends StatelessWidget {
  const MataKoApp({
    super.key,
    this.startAtLogin = false,
    this.initialLanguage = 'English',
  });

  final bool startAtLogin;
  final String initialLanguage;

  @override
  Widget build(BuildContext context) {
    return ValueListenableBuilder<Locale>(
      valueListenable: AppLocale.current,
      builder: (context, locale, _) => MaterialApp(
        title: 'MataKo',
        debugShowCheckedModeBanner: false,
        locale: locale,
        supportedLocales: const [
          Locale('en'),
          Locale('fil'),
          Locale('ceb'),
        ],
        localizationsDelegates: const [
          AppLocalizations.delegate,
          GlobalMaterialLocalizations.delegate,
          GlobalWidgetsLocalizations.delegate,
          GlobalCupertinoLocalizations.delegate,
          CebuanoMaterialLocalizationsDelegate(),
          CebuanoWidgetsLocalizationsDelegate(),
          CebuanoCupertinoLocalizationsDelegate(),
        ],
        theme: ThemeData(
          colorScheme: ColorScheme.fromSeed(
            seedColor: AppColors.orange,
            primary: AppColors.orange,
            onPrimary: AppColors.navy,
            secondary: AppColors.navy,
            onSecondary: AppColors.white,
            surface: AppColors.warmWhite,
            onSurface: AppColors.navy,
          ),
          scaffoldBackgroundColor: AppColors.warmWhite,
          appBarTheme: const AppBarTheme(
            backgroundColor: AppColors.warmWhite,
            foregroundColor: AppColors.navy,
            centerTitle: false,
          ),
          inputDecorationTheme: InputDecorationTheme(
            filled: true,
            fillColor: AppColors.white,
            labelStyle: const TextStyle(color: AppColors.navy),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: AppColors.navy),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide:
                  BorderSide(color: AppColors.navy.withValues(alpha: 0.35)),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: AppColors.orange, width: 2),
            ),
          ),
          elevatedButtonTheme: ElevatedButtonThemeData(
            style: ElevatedButton.styleFrom(
              minimumSize: const Size.fromHeight(48),
              backgroundColor: AppColors.orange,
              foregroundColor: AppColors.navy,
              textStyle: const TextStyle(fontWeight: FontWeight.w600),
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12)),
            ),
          ),
          textTheme: ThemeData.light().textTheme.apply(
                bodyColor: AppColors.navy,
                displayColor: AppColors.navy,
              ),
        ),
        home: startAtLogin
            ? LoginScreen(language: initialLanguage)
            : const SplashScreen(),
      ),
    );
  }
}
