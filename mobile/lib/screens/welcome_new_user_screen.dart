import 'package:flutter/material.dart';

import '../l10n/app_localizations.dart';
import '../theme/app_colors.dart';
import 'home_screen.dart';
import 'quick_tour_screen.dart';

/// This welcome page is shown only immediately after a successful sign-up.
class WelcomeNewUserScreen extends StatelessWidget {
  const WelcomeNewUserScreen({super.key});

  void _goHome(BuildContext context) {
    Navigator.of(context).pushReplacement(
      MaterialPageRoute<void>(builder: (_) => const HomeScreen()),
    );
  }

  void _takeTour(BuildContext context) {
    Navigator.of(context).pushReplacement(
      MaterialPageRoute<void>(builder: (_) => const QuickTourScreen()),
    );
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        backgroundColor: AppColors.warmWhite,
        body: SafeArea(
          child: LayoutBuilder(
            builder: (context, constraints) {
              final height = constraints.maxHeight;
              return Stack(
                children: [
                  Positioned(
                    left: -12,
                    bottom: -height * 0.035,
                    width: constraints.maxWidth * 0.72,
                    height: height * 0.50,
                    child: Image.asset(
                      'assets/images/side.png',
                      fit: BoxFit.contain,
                      alignment: Alignment.bottomLeft,
                    ),
                  ),
                  Positioned(
                    top: 2,
                    left: 12,
                    child: IconButton(
                      tooltip: context.tr('Go to Home'),
                      onPressed: () => _goHome(context),
                      icon: const Icon(Icons.arrow_back),
                      color: AppColors.navy,
                    ),
                  ),
                  Positioned(
                    top: height * 0.065,
                    left: 24,
                    right: 20,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          context.tr('Hello,\nwelcome to'),
                          style: const TextStyle(
                            color: AppColors.orange,
                            fontSize: 32,
                            height: 1.02,
                          ),
                        ),
                        const Text(
                          'MataKo!',
                          style: TextStyle(
                            color: AppColors.navy,
                            fontSize: 48,
                            height: 1.05,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                        const SizedBox(height: 14),
                        Text.rich(
                          TextSpan(
                            style: const TextStyle(
                              color: AppColors.navy,
                              fontSize: 14,
                              height: 1.55,
                            ),
                            children: [
                              TextSpan(
                                text: context.tr(
                                    'Your personal companion in\nprotecting your eyes from\n'),
                              ),
                              TextSpan(
                                text: context.tr('Digital Eye Strain.'),
                                style: const TextStyle(
                                    fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                  Positioned(
                    top: height * 0.345,
                    right: 22,
                    width: constraints.maxWidth * 0.52,
                    child: Text.rich(
                      TextSpan(
                        style: const TextStyle(
                          color: AppColors.navy,
                          fontSize: 14,
                          height: 1.55,
                        ),
                        children: [
                          TextSpan(text: context.tr('Would you like a ')),
                          TextSpan(
                            text: context.tr('quick tour'),
                            style: const TextStyle(fontWeight: FontWeight.bold),
                          ),
                          TextSpan(
                            text: context.tr(
                                ' of the app features, or go directly to your eye care dashboard?'),
                          ),
                        ],
                      ),
                      textAlign: TextAlign.right,
                    ),
                  ),
                  Positioned(
                    top: height * 0.50,
                    right: 20,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        SizedBox(
                          width: 174,
                          height: 46,
                          child: ElevatedButton(
                            onPressed: () => _takeTour(context),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.orange,
                              foregroundColor: AppColors.white,
                              elevation: 3,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(14),
                              ),
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Text(context.tr('Take a Tour'),
                                    style: const TextStyle(fontSize: 17)),
                                const SizedBox(width: 6),
                                const Icon(Icons.chevron_right, size: 24),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 16),
                        SizedBox(
                          width: 122,
                          height: 42,
                          child: ElevatedButton(
                            onPressed: () => _goHome(context),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.navy,
                              foregroundColor: AppColors.white,
                              elevation: 2,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(13),
                              ),
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Text(context.tr('Go to Home'),
                                    style: const TextStyle(fontSize: 13)),
                                const SizedBox(width: 3),
                                const Icon(Icons.chevron_right, size: 19),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              );
            },
          ),
        ),
      );
}
