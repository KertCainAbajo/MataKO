import 'package:flutter/material.dart';

import '../l10n/app_localizations.dart';
import '../theme/app_colors.dart';

class ResultScreen extends StatelessWidget {
  const ResultScreen({super.key, required this.data});
  final Map<String, dynamic> data;

  @override
  Widget build(BuildContext context) {
    final assessment = data['assessment'] as Map<String, dynamic>;
    final risk = assessment['risk_level'].toString();
    final recommendations = (data['recommendations'] as List<dynamic>? ?? [])
        .map((item) => context.tr(item.toString()))
        .toList();
    final color = risk == 'MEDIUM' ? AppColors.orange : AppColors.navy;
    return Scaffold(
      appBar: AppBar(title: Text(context.tr('Your result'))),
      body: ListView(padding: const EdgeInsets.all(24), children: [
        const Icon(Icons.visibility_outlined,
            size: 60, color: AppColors.orange),
        const SizedBox(height: 12),
        Text(risk,
            textAlign: TextAlign.center,
            style: Theme.of(context)
                .textTheme
                .headlineLarge
                ?.copyWith(color: color, fontWeight: FontWeight.bold)),
        Text(
            context
                .tr('Digital eye strain risk · {score} of 15 points')
                .replaceFirst('{score}', assessment['total_score'].toString()),
            textAlign: TextAlign.center),
        const SizedBox(height: 24),
        Text(context.tr('Suggestions for you'),
            style: Theme.of(context).textTheme.titleLarge),
        const SizedBox(height: 8),
        ...recommendations.map((item) => Card(
            color: AppColors.white,
            child: ListTile(
                leading: const Icon(Icons.check_circle_outline,
                    color: AppColors.orange),
                title: Text(item)))),
        const SizedBox(height: 12),
        Text(
            context.tr(
                'This screening result is not a diagnosis. If symptoms persist or concern you, consider speaking with an eye care professional.'),
            style: const TextStyle(color: Colors.black54)),
        const SizedBox(height: 20),
        ElevatedButton(
            onPressed: () => Navigator.pop(context),
            child: Text(context.tr('Back to dashboard'))),
      ]),
    );
  }
}
