import 'package:flutter/material.dart';

import '../l10n/app_localizations.dart';
import '../theme/app_colors.dart';
import 'welcome_screen.dart';

/// Brief animated brand intro before the welcome screen.
class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;
  bool _isContinuing = false;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1500),
    )..addStatusListener((status) {
        if (status == AnimationStatus.completed) {
          _openWelcome();
        }
      });
  }

  void _openWelcome() {
    if (_isContinuing || !mounted) return;
    _isContinuing = true;
    Navigator.of(context).pushReplacement(
      PageRouteBuilder<void>(
        pageBuilder: (_, animation, __) => const WelcomeScreen(),
        transitionsBuilder: (_, animation, __, child) =>
            FadeTransition(opacity: animation, child: child),
        transitionDuration: const Duration(milliseconds: 350),
      ),
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        backgroundColor: AppColors.warmWhite,
        body: SafeArea(
          child: LayoutBuilder(
            builder: (context, constraints) {
              final upwardTravel = constraints.maxHeight * 0.30;
              final rise = CurvedAnimation(
                parent: _controller,
                curve: Curves.easeInOutCubic,
              );

              return GestureDetector(
                behavior: HitTestBehavior.opaque,
                onTap: () {
                  if (!_controller.isAnimating && !_controller.isCompleted) {
                    _controller.forward();
                  }
                },
                child: Stack(
                  children: [
                    Center(
                      child: AnimatedBuilder(
                        animation: rise,
                        child: Image.asset(
                          'assets/images/mata.png',
                          width: 230,
                          fit: BoxFit.contain,
                        ),
                        builder: (context, logo) => Transform.translate(
                          offset: Offset(0, -upwardTravel * rise.value),
                          child: Transform.scale(
                            scale: 1 - (0.08 * rise.value),
                            child: logo,
                          ),
                        ),
                      ),
                    ),
                    Positioned(
                      left: 0,
                      right: 0,
                      bottom: 24,
                      child: AnimatedBuilder(
                        animation: _controller,
                        builder: (context, child) => Opacity(
                          opacity: 1 - _controller.value,
                          child: child,
                        ),
                        child: Text(
                          context.tr('Tap to continue'),
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                            color: AppColors.navy,
                            fontSize: 16,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
        ),
      );
}
