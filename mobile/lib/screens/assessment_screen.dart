import 'package:flutter/material.dart';

import '../l10n/app_localizations.dart';
import '../services/api_service.dart';
import 'result_screen.dart';

class AssessmentScreen extends StatefulWidget {
  const AssessmentScreen({super.key});
  @override
  State<AssessmentScreen> createState() => _AssessmentScreenState();
}

class _AssessmentScreenState extends State<AssessmentScreen> {
  static const symptoms = [
    'Eye pain',
    'Dry eyes',
    'Blurred vision',
    'Headache',
    'Eye fatigue'
  ];
  static const frequencies = ['None', 'Sometimes', 'Often', 'Always'];
  final Map<String, int> _answers = {
    for (final symptom in symptoms) symptom: 0
  };
  bool _loading = false;

  Future<void> _submit() async {
    setState(() => _loading = true);
    try {
      final result = await ApiService().submitAssessment(_answers);
      if (!mounted) return;
      await Navigator.push(context,
          MaterialPageRoute(builder: (_) => ResultScreen(data: result)));
      if (mounted) Navigator.pop(context, true);
    } catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
            content: Text(
                context.tr(error.toString().replaceFirst('Exception: ', '')))));
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        appBar: AppBar(title: Text(context.tr('Self-assessment'))),
        body: ListView(padding: const EdgeInsets.all(20), children: [
          Text(context.tr(
              'Over the past week, how often have you experienced each symptom?')),
          const SizedBox(height: 12),
          ...symptoms.map((symptom) => Card(
              margin: const EdgeInsets.only(bottom: 10),
              child: Padding(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 12),
                  child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(context.tr(symptom),
                            style:
                                const TextStyle(fontWeight: FontWeight.w600)),
                        DropdownButton<int>(
                            isExpanded: true,
                            value: _answers[symptom],
                            items: List.generate(
                                4,
                                (i) => DropdownMenuItem(
                                    value: i,
                                    child: Text(
                                        '$i — ${context.tr(frequencies[i])}'))),
                            onChanged: (value) =>
                                setState(() => _answers[symptom] = value ?? 0)),
                      ])))),
          const SizedBox(height: 8),
          ElevatedButton(
              onPressed: _loading ? null : _submit,
              child: Text(context.tr(_loading ? 'Saving…' : 'See my result'))),
        ]),
      );
}
