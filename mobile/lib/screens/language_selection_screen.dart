import 'package:flutter/material.dart';

import '../l10n/app_locale.dart';
import '../l10n/app_localizations.dart';
import '../services/api_service.dart';
import '../theme/app_colors.dart';
import 'login_screen.dart';

/// Lets the user choose the language used on the login screen.
class LanguageSelectionScreen extends StatefulWidget {
  const LanguageSelectionScreen({super.key, required this.initialRole});

  final String initialRole;

  @override
  State<LanguageSelectionScreen> createState() =>
      _LanguageSelectionScreenState();
}

class _LanguageSelectionScreenState extends State<LanguageSelectionScreen> {
  String? _selectedLanguage;
  final _api = ApiService();

  Future<void> _openLogin(String language) async {
    await _api.savePreferredLanguage(language);
    AppLocale.setLanguage(language);
    if (!mounted) return;
    Navigator.of(context).push(
      MaterialPageRoute<void>(
        builder: (_) => LoginScreen(
          language: language,
          initialRole: widget.initialRole,
        ),
      ),
    );
  }

  Future<void> _selectLanguage(String language) async {
    setState(() => _selectedLanguage = language);
    AppLocale.setLanguage(language);
    await _api.savePreferredLanguage(language);
  }

  @override
  Widget build(BuildContext context) {
    final strings = AppLocalizations.of(context);
    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      body: SafeArea(
        child: Column(
          children: [
            Container(
              height: 56,
              color: AppColors.white,
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Row(
                children: [
                  IconButton(
                    tooltip: strings.text('Back'),
                    onPressed: () => Navigator.of(context).pop(),
                    icon: const Icon(Icons.arrow_back),
                    color: AppColors.navy,
                  ),
                  const Spacer(),
                  const Icon(
                    Icons.remove_red_eye_outlined,
                    color: AppColors.orange,
                    size: 28,
                  ),
                  const SizedBox(width: 6),
                  const Text(
                    'MataKo',
                    style: TextStyle(
                      color: AppColors.navy,
                      fontSize: 20,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const Spacer(),
                  const SizedBox(width: 48),
                ],
              ),
            ),
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.fromLTRB(24, 20, 24, 12),
                child: Column(
                  children: [
                    Image.asset(
                      'assets/images/language.png',
                      width: 200,
                      height: 185,
                      fit: BoxFit.contain,
                    ),
                    const SizedBox(height: 16),
                    Text(
                      strings.text(
                          "Select a language you're\nmost comfortable with."),
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        color: AppColors.navy,
                        fontSize: 21,
                        height: 1.25,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 14),
                    Text(
                      strings.text('Choose your language:'),
                      style: const TextStyle(color: Color(0xFF666666)),
                    ),
                    const SizedBox(height: 12),
                    DropdownButtonFormField<String>(
                      initialValue: _selectedLanguage,
                      hint: Text(strings.text('Select a language')),
                      isExpanded: true,
                      decoration: InputDecoration(
                        filled: true,
                        fillColor: AppColors.white,
                        contentPadding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 4,
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(9),
                          borderSide:
                              const BorderSide(color: Color(0xFFD8D8D8)),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(9),
                          borderSide: const BorderSide(
                            color: AppColors.orange,
                            width: 1.5,
                          ),
                        ),
                      ),
                      items: const [
                        DropdownMenuItem(
                          value: 'English',
                          child: _LanguageOption(
                            flag: '\u{1F1FA}\u{1F1F8}',
                            name: 'English',
                          ),
                        ),
                        DropdownMenuItem(
                          value: 'Filipino',
                          child: _LanguageOption(
                            flag: '\u{1F1F5}\u{1F1ED}',
                            name: 'Filipino',
                          ),
                        ),
                        DropdownMenuItem(
                          value: 'Cebuano',
                          child: _LanguageOption(
                            flag: '\u{1F1F5}\u{1F1ED}',
                            name: 'Cebuano',
                          ),
                        ),
                      ],
                      onChanged: (language) {
                        if (language != null) {
                          _selectLanguage(language);
                        }
                      },
                    ),
                  ],
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: Column(
                children: [
                  SizedBox(
                    width: 156,
                    height: 44,
                    child: ElevatedButton(
                      onPressed: _selectedLanguage == null
                          ? null
                          : () => _openLogin(_selectedLanguage!),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.orange,
                        foregroundColor: AppColors.white,
                        elevation: 3,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                        ),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(strings.text('Proceed'),
                              style: const TextStyle(fontSize: 18)),
                          const SizedBox(width: 6),
                          const Icon(Icons.chevron_right, size: 24),
                        ],
                      ),
                    ),
                  ),
                  TextButton(
                    onPressed: () => _openLogin('English'),
                    child: Text(
                      strings.text('Skip for now'),
                      style: const TextStyle(
                        color: Color(0xFF777777),
                        decoration: TextDecoration.underline,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _LanguageOption extends StatelessWidget {
  const _LanguageOption({required this.flag, required this.name});

  final String flag;
  final String name;

  @override
  Widget build(BuildContext context) => Row(
        children: [
          Text(flag, style: const TextStyle(fontSize: 20)),
          const SizedBox(width: 10),
          Text(name),
        ],
      );
}
