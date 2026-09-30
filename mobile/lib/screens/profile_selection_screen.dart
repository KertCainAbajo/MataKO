import 'package:flutter/material.dart';

import '../l10n/app_locale.dart';
import '../l10n/app_localizations.dart';
import '../theme/app_colors.dart';
import 'login_screen.dart';
import 'language_selection_screen.dart';

/// Lets a new user choose a profile before creating an account.
class ProfileSelectionScreen extends StatefulWidget {
  const ProfileSelectionScreen({super.key});

  @override
  State<ProfileSelectionScreen> createState() => _ProfileSelectionScreenState();
}

class _ProfileSelectionScreenState extends State<ProfileSelectionScreen> {
  String _selectedRole = 'student';

  void _proceed() {
    Navigator.of(context).push(
      MaterialPageRoute<void>(
        builder: (_) => LanguageSelectionScreen(initialRole: _selectedRole),
      ),
    );
  }

  void _skipForNow() {
    Navigator.of(context).pushReplacement(
      MaterialPageRoute<void>(
        builder: (_) => LoginScreen(
          language: AppLocale.languageName(Localizations.localeOf(context)),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) => Scaffold(
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
                      tooltip: context.tr('Back'),
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
                  padding: const EdgeInsets.fromLTRB(20, 22, 20, 18),
                  child: Column(
                    children: [
                      Text(
                        context.tr('Select Your Profile Type'),
                        textAlign: TextAlign.center,
                        style: const TextStyle(
                          color: AppColors.navy,
                          fontSize: 21,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        context.tr('This will help us tailor your experience.'),
                        textAlign: TextAlign.center,
                        style: const TextStyle(color: Color(0xFF666666)),
                      ),
                      const SizedBox(height: 20),
                      _ProfileCard(
                        title: context.tr('Select Student'),
                        imagePath: 'assets/images/student.png',
                        selected: _selectedRole == 'student',
                        onTap: () => setState(() => _selectedRole = 'student'),
                      ),
                      const SizedBox(height: 12),
                      _ProfileCard(
                        title: context.tr('Select Professional'),
                        imagePath: 'assets/images/employee.png',
                        selected: _selectedRole == 'professional',
                        onTap: () =>
                            setState(() => _selectedRole = 'professional'),
                      ),
                      const SizedBox(height: 24),
                      SizedBox(
                        width: 156,
                        height: 44,
                        child: ElevatedButton(
                          onPressed: _proceed,
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
                              Text(context.tr('Proceed'),
                                  style: const TextStyle(fontSize: 18)),
                              const SizedBox(width: 6),
                              const Icon(Icons.chevron_right, size: 24),
                            ],
                          ),
                        ),
                      ),
                      TextButton(
                        onPressed: _skipForNow,
                        child: Text(
                          context.tr('Skip for now'),
                          style: const TextStyle(
                            color: Color(0xFF777777),
                            decoration: TextDecoration.underline,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      );
}

class _ProfileCard extends StatelessWidget {
  const _ProfileCard({
    required this.title,
    required this.imagePath,
    required this.selected,
    required this.onTap,
  });

  final String title;
  final String imagePath;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) => Material(
        color: selected ? const Color(0xFFE0E0E0) : AppColors.white,
        borderRadius: BorderRadius.circular(12),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(12),
          child: Container(
            height: 232,
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              border: Border.all(
                color: selected
                    ? const Color(0xFFD6D6D6)
                    : const Color(0xFFE2E2E2),
              ),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Stack(
              children: [
                Column(
                  children: [
                    Expanded(
                      child: Center(
                        child: Stack(
                          alignment: Alignment.center,
                          children: [
                            Container(
                              width: 150,
                              height: 150,
                              decoration: const BoxDecoration(
                                color: Color(0xFFFFC98F),
                                shape: BoxShape.circle,
                              ),
                            ),
                            Image.asset(
                              imagePath,
                              width: 150,
                              height: 150,
                              fit: BoxFit.contain,
                            ),
                          ],
                        ),
                      ),
                    ),
                    SizedBox(
                      width: double.infinity,
                      height: 48,
                      child: ElevatedButton(
                        onPressed: onTap,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.navy,
                          foregroundColor: AppColors.white,
                          elevation: 2,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(7),
                          ),
                        ),
                        child: Text(
                          title,
                          style: const TextStyle(fontSize: 17),
                        ),
                      ),
                    ),
                  ],
                ),
                Positioned(
                  top: 0,
                  right: 0,
                  child: Icon(
                    selected
                        ? Icons.radio_button_checked
                        : Icons.radio_button_unchecked,
                    size: 20,
                    color: selected
                        ? const Color(0xFF999999)
                        : const Color(0xFFE0E0E0),
                  ),
                ),
              ],
            ),
          ),
        ),
      );
}
