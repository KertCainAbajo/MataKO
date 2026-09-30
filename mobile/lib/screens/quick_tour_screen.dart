import 'package:flutter/material.dart';

import '../l10n/app_localizations.dart';
import '../theme/app_colors.dart';
import 'home_screen.dart';

/// A short three-step tour of the main MataKo features.
class QuickTourScreen extends StatefulWidget {
  const QuickTourScreen({super.key});

  @override
  State<QuickTourScreen> createState() => _QuickTourScreenState();
}

class _QuickTourScreenState extends State<QuickTourScreen> {
  final _pageController = PageController();
  int _currentPage = 0;

  static const _steps = [
    _TourStep(
      icon: Icons.remove_red_eye_outlined,
      title: 'Check how your eyes feel',
      description:
          'Answer a few questions about common digital eye strain symptoms.',
    ),
    _TourStep(
      icon: Icons.insights_outlined,
      title: 'Understand your result',
      description:
          'See your risk level and get simple recommendations based on your answers.',
    ),
    _TourStep(
      icon: Icons.timer_outlined,
      title: 'Build healthier screen habits',
      description:
          'Use your history and 20-20-20 break reminders to care for your eyes.',
    ),
  ];

  void _finishTour() {
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute<void>(builder: (_) => const HomeScreen()),
      (_) => false,
    );
  }

  void _nextPage() {
    if (_currentPage == _steps.length - 1) {
      _finishTour();
      return;
    }
    _pageController.nextPage(
      duration: const Duration(milliseconds: 250),
      curve: Curves.easeInOut,
    );
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        backgroundColor: AppColors.warmWhite,
        appBar: AppBar(
          title: Text(context.tr('Quick Tour')),
          actions: [
            TextButton(onPressed: _finishTour, child: Text(context.tr('Skip'))),
          ],
        ),
        body: SafeArea(
          child: Column(
            children: [
              Expanded(
                child: PageView.builder(
                  controller: _pageController,
                  itemCount: _steps.length,
                  onPageChanged: (page) => setState(() => _currentPage = page),
                  itemBuilder: (context, index) =>
                      _TourPage(step: _steps[index]),
                ),
              ),
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: List.generate(
                  _steps.length,
                  (index) => AnimatedContainer(
                    duration: const Duration(milliseconds: 180),
                    margin: const EdgeInsets.symmetric(horizontal: 4),
                    width: index == _currentPage ? 22 : 8,
                    height: 8,
                    decoration: BoxDecoration(
                      color: index == _currentPage
                          ? AppColors.orange
                          : AppColors.navy.withValues(alpha: 0.25),
                      borderRadius: BorderRadius.circular(8),
                    ),
                  ),
                ),
              ),
              Padding(
                padding: const EdgeInsets.fromLTRB(24, 18, 24, 24),
                child: SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: ElevatedButton(
                    onPressed: _nextPage,
                    child: Text(
                      context.tr(_currentPage == _steps.length - 1
                          ? 'Go to Home'
                          : 'Next'),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      );
}

class _TourStep {
  const _TourStep({
    required this.icon,
    required this.title,
    required this.description,
  });

  final IconData icon;
  final String title;
  final String description;
}

class _TourPage extends StatelessWidget {
  const _TourPage({required this.step});

  final _TourStep step;

  @override
  Widget build(BuildContext context) => Padding(
        padding: const EdgeInsets.symmetric(horizontal: 36),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 144,
              height: 144,
              decoration: BoxDecoration(
                color: AppColors.orange.withValues(alpha: 0.12),
                shape: BoxShape.circle,
              ),
              child: Icon(step.icon, size: 72, color: AppColors.orange),
            ),
            const SizedBox(height: 32),
            Text(
              context.tr(step.title),
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: AppColors.navy,
                fontSize: 24,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 12),
            Text(
              context.tr(step.description),
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: Color(0xFF526174),
                fontSize: 16,
                height: 1.5,
              ),
            ),
          ],
        ),
      );
}
