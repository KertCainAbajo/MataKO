import 'package:flutter/material.dart';

import '../l10n/app_localizations.dart';
import '../theme/app_colors.dart';

/// Terms and privacy information opened from the sign-up form.
class TermsPrivacyScreen extends StatelessWidget {
  const TermsPrivacyScreen({
    super.key,
    this.initialTabIndex = 0,
    this.showConsentButton = true,
  });

  final int initialTabIndex;
  final bool showConsentButton;

  @override
  Widget build(BuildContext context) => DefaultTabController(
        length: 2,
        initialIndex: initialTabIndex,
        child: Scaffold(
          backgroundColor: AppColors.warmWhite,
          body: SafeArea(
            child: Column(
              children: [
                Container(
                  height: 52,
                  color: AppColors.white,
                  child: Row(
                    children: [
                      IconButton(
                        tooltip: context
                            .tr(showConsentButton ? 'Back to Sign Up' : 'Back'),
                        onPressed: () => Navigator.of(context).pop(false),
                        icon: const Icon(Icons.arrow_back),
                        color: const Color(0xFF475467),
                      ),
                      Text(
                        context
                            .tr(showConsentButton ? 'Back to Sign Up' : 'Back'),
                        style: const TextStyle(
                          color: Color(0xFF1D2939),
                          fontSize: 14,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ),
                Material(
                  color: const Color(0xFFF1EEEE),
                  child: TabBar(
                    labelColor: AppColors.navy,
                    unselectedLabelColor: const Color(0xFF777777),
                    indicatorColor: AppColors.orange,
                    indicatorWeight: 2,
                    labelStyle: const TextStyle(fontSize: 13),
                    tabs: [
                      Tab(text: context.tr('Terms of Service')),
                      Tab(text: context.tr('Privacy Policy')),
                    ],
                  ),
                ),
                const Expanded(
                  child: TabBarView(
                    children: [
                      _TermsContent(),
                      _PrivacyContent(),
                    ],
                  ),
                ),
                Container(
                  color: AppColors.white,
                  padding: const EdgeInsets.fromLTRB(28, 12, 28, 14),
                  child: SizedBox(
                    width: double.infinity,
                    height: 46,
                    child: ElevatedButton(
                      onPressed: () =>
                          Navigator.of(context).pop(showConsentButton),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.orange,
                        foregroundColor: AppColors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                      child: Text(
                        context.tr(showConsentButton
                            ? 'I Agree and Continue'
                            : 'Done'),
                        style: const TextStyle(fontWeight: FontWeight.w600),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      );
}

class _TermsContent extends StatelessWidget {
  const _TermsContent();

  @override
  Widget build(BuildContext context) => const _LegalDocument(
        title: 'Terms of Service',
        children: [
          _LegalParagraph(
            'Welcome to MataKo, your personal guide to managing Digital Eye '
            'Strain (DES). By using MataKo, you agree to these terms. Please '
            'read them carefully.',
          ),
          _LegalHeading('1. Acceptance of Terms'),
          _LegalParagraph(
            'By accessing and using MataKo, you confirm that you are at least '
            '18 years old and agree to be bound by these Terms of Service.',
          ),
          _LegalHeading('2. Purpose of the Service'),
          _LegalParagraph(
            'MataKo helps adults and working professionals self-assess '
            'symptoms and manage Digital Eye Strain through self-evaluation '
            'tools, behavior strategies, reminders, and educational content.',
          ),
          _LegalHeading('3. User Responsibilities'),
          _LegalParagraph(
            'You are responsible for keeping your MataKo account confidential '
            'and using the app lawfully and respectfully. Assessments and '
            'feedback are informational only and do not replace medical advice.',
          ),
          _LegalHeading('4. Health Disclaimer'),
          _LegalParagraph(
            'MataKo is not intended to diagnose, treat, or replace professional '
            'medical advice. If you have ongoing or severe symptoms, consult a '
            'licensed eye care professional.',
          ),
          _LegalHeading('5. Content Usage'),
          _LegalParagraph(
            'MataKo content, including ergonomic suggestions and educational '
            'materials, is provided for personal informational use. Do not '
            'reproduce or redistribute it without permission.',
          ),
        ],
      );
}

class _PrivacyContent extends StatelessWidget {
  const _PrivacyContent();

  @override
  Widget build(BuildContext context) => const _LegalDocument(
        title: 'Privacy Policy',
        children: [
          _LegalParagraph(
            'At MataKo, we value your privacy and are committed to protecting '
            'your personal and health-related information.',
          ),
          _LegalHeading('1. Information We Collect'),
          _LegalParagraph(
            'Personal information: name, email address, phone number, and age. '
            'Health data: your self-assessment responses, including Digital '
            'Eye Strain symptoms. App usage data may include interactions with '
            'features, reminders, and educational tips.',
          ),
          _LegalHeading('2. How We Use Your Data'),
          _LegalParagraph(
            'We use information to provide assessment results, personalized '
            'care suggestions, reminders, and educational content, and to '
            'maintain and improve the app.',
          ),
          _LegalHeading('3. Data Sharing'),
          _LegalParagraph(
            'MataKo does not sell your personal information. Data may be used '
            'in aggregated form to evaluate and improve the service. We do not '
            'share identifying health information without your consent.',
          ),
          _LegalHeading('4. Storage and Security'),
          _LegalParagraph(
            'Your information is stored in the database configured for the '
            'MataKo service. Access is limited to the service and its authorized '
            'operators. Security protections depend on the environment where '
            'the service is deployed.',
          ),
          _LegalHeading('5. Your Rights'),
          _LegalParagraph(
            'You may request access to or correction of your account information. '
            'You may also request account deletion from the MataKo service '
            'administrator.',
          ),
        ],
      );
}

class _LegalDocument extends StatelessWidget {
  const _LegalDocument({required this.title, required this.children});

  final String title;
  final List<Widget> children;

  @override
  Widget build(BuildContext context) => SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(16, 20, 16, 24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              context.tr(title),
              style: const TextStyle(
                color: AppColors.navy,
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 12),
            ...children,
          ],
        ),
      );
}

class _LegalHeading extends StatelessWidget {
  const _LegalHeading(this.text);

  final String text;

  @override
  Widget build(BuildContext context) => Padding(
        padding: const EdgeInsets.only(top: 14, bottom: 6),
        child: Text(
          context.tr(text),
          style: const TextStyle(
            color: AppColors.navy,
            fontSize: 14,
            fontWeight: FontWeight.w500,
          ),
        ),
      );
}

class _LegalParagraph extends StatelessWidget {
  const _LegalParagraph(this.text);

  final String text;

  @override
  Widget build(BuildContext context) => Text(
        context.tr(text),
        style: const TextStyle(
          color: Color(0xFF666666),
          fontSize: 11,
          height: 1.6,
        ),
      );
}
