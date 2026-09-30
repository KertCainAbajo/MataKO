import 'package:flutter/material.dart';

import '../l10n/app_localizations.dart';
import '../theme/app_colors.dart';

/// In-app notification center. Actions connect to the matching app features.
class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({
    super.key,
    required this.onStartAssessment,
    required this.onViewCareTips,
  });

  final VoidCallback onStartAssessment;
  final VoidCallback onViewCareTips;

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  String _filter = 'All';

  bool get _showToday => _filter != 'Yesterday';
  bool get _showYesterday => _filter != 'Today';

  void _go(VoidCallback action) {
    Navigator.of(context).pop();
    action();
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        backgroundColor: const Color(0xFFF5F5F5),
        body: SafeArea(
          child: Column(
            children: [
              Container(
                height: 52,
                color: AppColors.white,
                child: Row(
                  children: [
                    IconButton(
                      tooltip: context.tr('Back'),
                      onPressed: () => Navigator.of(context).pop(),
                      icon: const Icon(Icons.arrow_back),
                      color: AppColors.navy,
                    ),
                    Expanded(
                      child: Text(
                        context.tr('Notification'),
                        textAlign: TextAlign.center,
                        style: const TextStyle(
                          color: AppColors.navy,
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                    PopupMenuButton<String>(
                      tooltip: context.tr('Filter notifications'),
                      icon: const Icon(Icons.filter_list, size: 20),
                      onSelected: (value) => setState(() => _filter = value),
                      itemBuilder: (context) => [
                        PopupMenuItem(
                            value: 'All', child: Text(context.tr('All'))),
                        PopupMenuItem(
                            value: 'Today', child: Text(context.tr('Today'))),
                        PopupMenuItem(
                          value: 'Yesterday',
                          child: Text(context.tr('Yesterday')),
                        ),
                      ],
                    ),
                    const SizedBox(width: 6),
                  ],
                ),
              ),
              Expanded(
                child: ListView(
                  padding: const EdgeInsets.fromLTRB(17, 12, 17, 24),
                  children: [
                    if (_showToday) ...[
                      const _DayDivider('Today'),
                      _NotificationCard(
                        icon: Icons.lightbulb_outline,
                        title: 'Screen Break Reminders',
                        message:
                            '👀 Time to rest your eyes! Look 30 feet away for 30 seconds.',
                        detail:
                            "You've been on screen for 1 hour. Take a short break and blink more!",
                        actionLabel: 'More eye care tips',
                        onAction: () => _go(widget.onViewCareTips),
                      ),
                    ],
                    if (_showYesterday) ...[
                      const _DayDivider('Yesterday'),
                      _NotificationCard(
                        icon: Icons.assignment_outlined,
                        title: 'Self-Assessment',
                        message:
                            "📝 Haven't checked in today? Take your quick eye health check-up now.",
                        actionLabel: 'Start Self-Assessment',
                        onAction: () => _go(widget.onStartAssessment),
                      ),
                      const _NotificationCard(
                        icon: Icons.emoji_events_outlined,
                        title: '🎉 Congratulations!',
                        message:
                            "You've taken 3 screen breaks today. Great job protecting your eyes!",
                      ),
                      const _NotificationCard(
                        icon: Icons.settings_outlined,
                        title: 'System Alert!',
                        message:
                            '⚙️ Update your screen break settings for better tracking.',
                      ),
                    ],
                    if (!_showToday && !_showYesterday)
                      Center(
                          child: Text(context.tr('No notifications to show.'))),
                  ],
                ),
              ),
            ],
          ),
        ),
      );
}

class _DayDivider extends StatelessWidget {
  const _DayDivider(this.label);

  final String label;

  @override
  Widget build(BuildContext context) => Padding(
        padding: const EdgeInsets.fromLTRB(0, 2, 0, 9),
        child: Center(
          child: Text(
            context.tr(label),
            style: const TextStyle(color: Color(0xFF777777), fontSize: 11),
          ),
        ),
      );
}

class _NotificationCard extends StatelessWidget {
  const _NotificationCard({
    required this.icon,
    required this.title,
    required this.message,
    this.detail,
    this.actionLabel,
    this.onAction,
  });

  final IconData icon;
  final String title;
  final String message;
  final String? detail;
  final String? actionLabel;
  final VoidCallback? onAction;

  @override
  Widget build(BuildContext context) => Container(
        margin: const EdgeInsets.only(bottom: 11),
        padding: const EdgeInsets.fromLTRB(10, 10, 10, 7),
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.circular(13),
          border: Border.all(color: const Color(0xFFE5E7EB)),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            CircleAvatar(
              radius: 13,
              backgroundColor: AppColors.orange,
              child: Icon(icon, color: AppColors.white, size: 16),
            ),
            const SizedBox(width: 9),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    context.tr(title),
                    style: const TextStyle(
                      color: AppColors.navy,
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 5),
                  Text(
                    context.tr(message),
                    style: const TextStyle(
                      color: Color(0xFF555555),
                      fontSize: 10,
                      height: 1.4,
                    ),
                  ),
                  if (detail != null) ...[
                    const SizedBox(height: 5),
                    Text(
                      context.tr(detail!),
                      style: const TextStyle(
                        color: Color(0xFF777777),
                        fontSize: 8,
                        fontStyle: FontStyle.italic,
                        height: 1.3,
                      ),
                    ),
                  ],
                  if (actionLabel != null)
                    Align(
                      alignment: Alignment.centerRight,
                      child: TextButton.icon(
                        onPressed: onAction,
                        style: TextButton.styleFrom(
                          foregroundColor: AppColors.orange,
                          padding: const EdgeInsets.symmetric(horizontal: 0),
                          minimumSize: const Size(0, 25),
                          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                          textStyle: const TextStyle(fontSize: 9),
                        ),
                        iconAlignment: IconAlignment.end,
                        label: Text(context.tr(actionLabel!)),
                        icon: const Icon(Icons.chevron_right, size: 15),
                      ),
                    ),
                ],
              ),
            ),
          ],
        ),
      );
}
