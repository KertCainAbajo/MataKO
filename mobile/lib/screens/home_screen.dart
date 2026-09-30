import 'dart:async';

import 'package:flutter/material.dart';

import '../l10n/app_locale.dart';
import '../l10n/app_localizations.dart';
import '../services/api_service.dart';
import '../theme/app_colors.dart';
import 'assessment_screen.dart';
import 'login_screen.dart';
import 'notifications_screen.dart';
import 'terms_privacy_screen.dart';

/// Main dashboard and the five sections in the bottom navigation bar.
class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final _api = ApiService();
  late Future<Map<String, dynamic>> _dashboard;
  late Future<Map<String, dynamic>> _profile;
  Timer? _reminder;
  bool _remindersOn = false;
  int _breakRemindersToday = 0;
  int _selectedTab = 0;

  @override
  void initState() {
    super.initState();
    _dashboard = _api.getDashboard();
    _profile = _api.getUser();
  }

  void _toggleReminder(bool enabled) {
    _reminder?.cancel();
    setState(() => _remindersOn = enabled);
    if (enabled) {
      _reminder = Timer.periodic(const Duration(minutes: 20), (_) {
        if (!mounted) return;
        setState(() => _breakRemindersToday++);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
                context.tr('Eye break: look 20 feet away for 20 seconds.')),
          ),
        );
      });
    }
  }

  Future<void> _refresh() async {
    setState(() => _dashboard = _api.getDashboard());
    await _dashboard;
  }

  Future<void> _startAssessment() async {
    final changed = await Navigator.of(context).push<bool>(
      MaterialPageRoute<bool>(builder: (_) => const AssessmentScreen()),
    );
    if (changed == true && mounted) await _refresh();
  }

  void _openNotifications() {
    Navigator.of(context).push(
      MaterialPageRoute<void>(
        builder: (_) => NotificationsScreen(
          onStartAssessment: _startAssessment,
          onViewCareTips: () {
            if (mounted) setState(() => _selectedTab = 3);
          },
        ),
      ),
    );
  }

  Future<void> _logout() async {
    _reminder?.cancel();
    await _api.clearToken();
    if (!mounted) return;
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute<void>(
        builder: (_) => LoginScreen(
          language: AppLocale.languageName(Localizations.localeOf(context)),
        ),
      ),
      (_) => false,
    );
  }

  void _openLegal(int tabIndex) {
    Navigator.of(context).push(
      MaterialPageRoute<void>(
        builder: (_) => TermsPrivacyScreen(
          initialTabIndex: tabIndex,
          showConsentButton: false,
        ),
      ),
    );
  }

  List<Map<String, dynamic>> _assessmentRows(
    Map<String, dynamic>? data,
  ) {
    final rawRows = data?['assessments'] as List<dynamic>? ?? [];
    return rawRows
        .whereType<Map>()
        .map((row) => Map<String, dynamic>.from(row))
        .toList();
  }

  Color _riskColor(String risk) {
    switch (risk.toUpperCase()) {
      case 'HIGH':
        return const Color(0xFFB54708);
      case 'MEDIUM':
        return AppColors.orange;
      default:
        return AppColors.navy;
    }
  }

  String _dateLabel(Map<String, dynamic> row) {
    final date =
        DateTime.tryParse(row['created_at']?.toString() ?? '')?.toLocal();
    if (date == null) return context.tr('Date unavailable');
    return '${date.month}/${date.day}/${date.year}';
  }

  @override
  void dispose() {
    _reminder?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        backgroundColor: AppColors.warmWhite,
        appBar: AppBar(
          toolbarHeight: 56,
          backgroundColor: AppColors.white,
          automaticallyImplyLeading: false,
          leading: _selectedTab == 4
              ? IconButton(
                  tooltip: context.tr('Back to Home'),
                  onPressed: () => setState(() => _selectedTab = 0),
                  icon: const Icon(Icons.arrow_back),
                  color: AppColors.navy,
                )
              : null,
          titleSpacing: 16,
          title: Image.asset(
            'assets/images/atam.png',
            width: 116,
            height: 42,
            fit: BoxFit.contain,
            alignment: Alignment.centerLeft,
          ),
          actions: [
            IconButton(
              tooltip: context.tr('Notifications'),
              onPressed: _openNotifications,
              icon: const Icon(Icons.notifications, color: AppColors.orange),
            ),
            Padding(
              padding: const EdgeInsets.only(right: 12),
              child: IconButton(
                tooltip: context.tr('Your profile and settings'),
                onPressed: () => setState(() => _selectedTab = 4),
                icon: _profileAvatar(),
              ),
            ),
          ],
        ),
        body: _selectedTab == 0
            ? _homeTab()
            : _selectedTab == 1
                ? _progressTab()
                : _selectedTab == 2
                    ? _eyeCareTab()
                    : _selectedTab == 3
                        ? _tipsTab()
                        : _settingsTab(),
        bottomNavigationBar: BottomNavigationBar(
          currentIndex: _selectedTab,
          onTap: (index) => setState(() => _selectedTab = index),
          type: BottomNavigationBarType.fixed,
          backgroundColor: AppColors.white,
          selectedItemColor: AppColors.orange,
          unselectedItemColor: const Color(0xFF9A9A9A),
          selectedFontSize: 10,
          unselectedFontSize: 10,
          items: [
            BottomNavigationBarItem(
                icon: const Icon(Icons.home), label: context.tr('Home')),
            BottomNavigationBarItem(
              icon: const Icon(Icons.show_chart),
              label: context.tr('Progress'),
            ),
            BottomNavigationBarItem(
              icon: const Icon(Icons.visibility),
              label: context.tr('Eye Care'),
            ),
            BottomNavigationBarItem(
                icon: const Icon(Icons.favorite), label: context.tr('Tips')),
            BottomNavigationBarItem(
              icon: const Icon(Icons.settings),
              label: context.tr('Settings'),
            ),
          ],
        ),
      );

  Widget _profileAvatar() => FutureBuilder<Map<String, dynamic>>(
        future: _profile,
        builder: (context, snapshot) {
          final user = snapshot.data?['user'] as Map<String, dynamic>?;
          final name = user?['name']?.toString() ?? '';
          final initial = name.isEmpty ? 'U' : name[0].toUpperCase();
          return CircleAvatar(
            radius: 16,
            backgroundColor: AppColors.navy,
            child: Text(
              initial,
              style: const TextStyle(
                color: AppColors.white,
                fontSize: 13,
                fontWeight: FontWeight.bold,
              ),
            ),
          );
        },
      );

  Widget _homeTab() => FutureBuilder<Map<String, dynamic>>(
        future: _dashboard,
        builder: (context, snapshot) {
          final rows = _assessmentRows(snapshot.data);
          final profile = _profile;
          return RefreshIndicator(
            onRefresh: _refresh,
            child: ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.fromLTRB(20, 18, 20, 24),
              children: [
                _greetingCard(profile),
                const SizedBox(height: 18),
                _featureCard(
                  icon: Icons.assignment_outlined,
                  title: context.tr('Start Self-Assessment'),
                  subtitle: context.tr('Quick check-up for your eye health'),
                  onTap: _startAssessment,
                ),
                _featureCard(
                  icon: Icons.favorite,
                  title: context.tr('View Care Tips'),
                  subtitle: context.tr('Learn how to protect your vision'),
                  onTap: () => setState(() => _selectedTab = 3),
                ),
                _featureCard(
                  icon: Icons.schedule,
                  title: context.tr('Set Screen Break Reminders'),
                  subtitle: context.tr('Schedule healthy breaks from screens'),
                  onTap: () => setState(() => _selectedTab = 2),
                ),
                _featureCard(
                  icon: Icons.settings,
                  title: context.tr('Eye Care Settings'),
                  subtitle: context.tr('Manage reminders and your account'),
                  onTap: () => setState(() => _selectedTab = 4),
                ),
                const SizedBox(height: 10),
                _todayProgress(rows),
                const SizedBox(height: 12),
                _eyeTipCard(),
                const SizedBox(height: 12),
                _recentActivity(rows, snapshot),
                const SizedBox(height: 12),
                Text(
                  context.tr(
                      'This self-assessment is for general guidance and is not a medical diagnosis.'),
                  style: const TextStyle(color: Colors.black54, fontSize: 12),
                ),
              ],
            ),
          );
        },
      );

  Widget _greetingCard(Future<Map<String, dynamic>> profile) => Container(
        height: 112,
        padding: const EdgeInsets.fromLTRB(18, 14, 8, 10),
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.circular(17),
        ),
        child: Row(
          children: [
            Expanded(
              child: FutureBuilder<Map<String, dynamic>>(
                future: profile,
                builder: (context, snapshot) {
                  final user = snapshot.data?['user'] as Map<String, dynamic>?;
                  final name = user?['name']?.toString().split(' ').first;
                  return Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        context.tr('Hi, {name}!').replaceFirst(
                              '{name}',
                              name?.isNotEmpty == true
                                  ? name!
                                  : context.tr('User'),
                            ),
                        style: const TextStyle(
                          color: AppColors.navy,
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        context.tr('Ready to take care of your eyes today?'),
                        style: const TextStyle(
                          color: Color(0xFF666666),
                          fontSize: 13,
                          height: 1.3,
                        ),
                      ),
                    ],
                  );
                },
              ),
            ),
            Image.asset(
              'assets/images/student.png',
              width: 112,
              height: 104,
              fit: BoxFit.contain,
            ),
          ],
        ),
      );

  Widget _featureCard({
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) =>
      Card(
        color: AppColors.navy,
        elevation: 3,
        shadowColor: AppColors.navy.withValues(alpha: 0.2),
        margin: const EdgeInsets.only(bottom: 11),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(14),
          child: SizedBox(
            height: 100,
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 19,
                    backgroundColor: AppColors.white,
                    child: Icon(icon, color: AppColors.orange, size: 21),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(
                          title,
                          style: const TextStyle(
                            color: AppColors.white,
                            fontWeight: FontWeight.bold,
                            fontSize: 15,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          subtitle,
                          style: const TextStyle(
                            color: Color(0xFFDCE4EA),
                            fontSize: 11,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const Icon(Icons.chevron_right, color: AppColors.white),
                ],
              ),
            ),
          ),
        ),
      );

  Widget _todayProgress(List<Map<String, dynamic>> rows) {
    final now = DateTime.now();
    final todayCount = rows.where((row) {
      final date =
          DateTime.tryParse(row['created_at']?.toString() ?? '')?.toLocal();
      return date != null &&
          date.year == now.year &&
          date.month == now.month &&
          date.day == now.day;
    }).length;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(15),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            context.tr("Today's Progress"),
            style: const TextStyle(
              color: AppColors.navy,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(
                child: _progressNumber(
                  value: todayCount.toString(),
                  label: context.tr('Assessments today'),
                ),
              ),
              Expanded(
                child: _progressNumber(
                  value: _breakRemindersToday.toString(),
                  label: context.tr('Break reminders'),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _progressNumber({required String value, required String label}) =>
      Column(
        children: [
          Text(
            value,
            style: const TextStyle(
              color: AppColors.navy,
              fontSize: 21,
              fontWeight: FontWeight.w500,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            textAlign: TextAlign.center,
            style: const TextStyle(color: Color(0xFF666666), fontSize: 11),
          ),
        ],
      );

  Widget _eyeTipCard() => Container(
        padding: const EdgeInsets.all(15),
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.circular(15),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const CircleAvatar(
              radius: 17,
              backgroundColor: AppColors.orange,
              child: Icon(Icons.lightbulb_outline,
                  color: AppColors.white, size: 19),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    context.tr("Today's Eye Tip"),
                    style: const TextStyle(
                      color: AppColors.navy,
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 5),
                  Text(
                    context.tr(
                        'Remember to blink more often while using your screen. The 20-20-20 rule can help reduce eye strain.'),
                    style: const TextStyle(
                      color: Color(0xFF666666),
                      fontSize: 11,
                      height: 1.4,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      );

  Widget _recentActivity(
    List<Map<String, dynamic>> rows,
    AsyncSnapshot<Map<String, dynamic>> snapshot,
  ) {
    if (snapshot.connectionState == ConnectionState.waiting) {
      return const Center(child: CircularProgressIndicator());
    }
    if (snapshot.hasError) {
      return _whitePanel(
        title: context.tr('Recent Activity'),
        child: Row(
          children: [
            Expanded(child: Text(context.tr('Could not load your activity.'))),
            IconButton(onPressed: _refresh, icon: const Icon(Icons.refresh)),
          ],
        ),
      );
    }
    if (rows.isEmpty) {
      return _whitePanel(
        title: context.tr('Recent Activity'),
        child: Text(
          context.tr(
              'No assessments yet. Complete a self-assessment to see your activity here.'),
          style: const TextStyle(color: Color(0xFF526174), fontSize: 12),
        ),
      );
    }

    final latest = rows.first;
    return _whitePanel(
      title: context.tr('Recent Activity'),
      child: ListTile(
        contentPadding: EdgeInsets.zero,
        leading: const CircleAvatar(
          radius: 16,
          backgroundColor: AppColors.orange,
          child: Icon(Icons.check, color: AppColors.white, size: 18),
        ),
        title:
            Text('${context.tr('Self-assessment')}: ${latest['risk_level']}'),
        subtitle: Text(_dateLabel(latest)),
        trailing: TextButton(
          onPressed: _startAssessment,
          child: Text(context.tr('Retake')),
        ),
      ),
    );
  }

  Widget _whitePanel({required String title, required Widget child}) =>
      Container(
        padding: const EdgeInsets.all(15),
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.circular(15),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              title,
              style: const TextStyle(
                color: AppColors.navy,
                fontWeight: FontWeight.w600,
              ),
            ),
            const SizedBox(height: 8),
            child,
          ],
        ),
      );

  Widget _progressTab() => FutureBuilder<Map<String, dynamic>>(
        future: _dashboard,
        builder: (context, snapshot) {
          final rows = _assessmentRows(snapshot.data);
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snapshot.hasError) {
            return Center(
              child: TextButton.icon(
                onPressed: _refresh,
                icon: const Icon(Icons.refresh),
                label: Text(context.tr('Reload progress')),
              ),
            );
          }
          return ListView(
            padding: const EdgeInsets.all(20),
            children: [
              Text(
                context.tr('Your Progress'),
                style: const TextStyle(
                  color: AppColors.navy,
                  fontSize: 21,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 6),
              Text(context.tr('Review your recent eye strain assessments.')),
              const SizedBox(height: 18),
              _todayProgress(rows),
              const SizedBox(height: 18),
              _whitePanel(
                title: context.tr('Assessment History'),
                child: rows.isEmpty
                    ? Text(context
                        .tr('Your completed assessments will appear here.'))
                    : Column(
                        children: rows.map((row) {
                          final risk = row['risk_level'].toString();
                          return ListTile(
                            contentPadding: EdgeInsets.zero,
                            leading: Icon(
                              Icons.circle,
                              size: 12,
                              color: _riskColor(risk),
                            ),
                            title: Text(
                                '$risk · ${row['total_score']}${context.tr(' points')}'),
                            subtitle: Text(_dateLabel(row)),
                          );
                        }).toList(),
                      ),
              ),
              const SizedBox(height: 16),
              ElevatedButton.icon(
                onPressed: _startAssessment,
                icon: const Icon(Icons.assignment_outlined),
                label: Text(context.tr('Start Self-Assessment')),
              ),
            ],
          );
        },
      );

  Widget _eyeCareTab() => ListView(
        padding: const EdgeInsets.all(20),
        children: [
          Text(
            context.tr('Eye Care'),
            style: const TextStyle(
              color: AppColors.navy,
              fontSize: 21,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(context.tr('Small, regular breaks can help during screen use.')),
          const SizedBox(height: 18),
          Container(
            decoration: BoxDecoration(
              color: AppColors.white,
              borderRadius: BorderRadius.circular(15),
            ),
            child: SwitchListTile.adaptive(
              activeThumbColor: AppColors.orange,
              title: Text(context.tr('20-20-20 break reminder')),
              subtitle: Text(
                _remindersOn
                    ? context.tr(
                        'Reminder repeats every 20 minutes while MataKo is open.')
                    : context.tr(
                        'Turn this on to receive an in-app break reminder.'),
              ),
              value: _remindersOn,
              onChanged: _toggleReminder,
            ),
          ),
          const SizedBox(height: 14),
          _whitePanel(
            title: context.tr('The 20-20-20 rule'),
            child: Text(
              context.tr(
                  'Every 20 minutes, look at something about 20 feet away for at least 20 seconds.'),
              style: const TextStyle(height: 1.5),
            ),
          ),
        ],
      );

  Widget _tipsTab() => ListView(
        padding: const EdgeInsets.all(20),
        children: [
          Text(
            context.tr('Care Tips'),
            style: const TextStyle(
              color: AppColors.navy,
              fontSize: 21,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(context.tr('Simple habits for more comfortable screen use.')),
          const SizedBox(height: 16),
          _tip(
            Icons.visibility_outlined,
            context.tr('Follow the 20-20-20 rule'),
            context.tr(
                'Every 20 minutes, look at something about 20 feet away for at least 20 seconds.'),
          ),
          _tip(
            Icons.remove_red_eye_outlined,
            context.tr('Blink more often'),
            context.tr(
                'Remember to blink more often while using your screen. The 20-20-20 rule can help reduce eye strain.'),
          ),
          _tip(
            Icons.wb_sunny_outlined,
            context.tr('Reduce glare'),
            context.tr(
                'Adjust screen brightness and position lights to avoid reflections.'),
          ),
          _tip(
            Icons.chair_outlined,
            context.tr('Set up your workspace'),
            context.tr(
                'Keep a comfortable viewing distance and sit with relaxed posture.'),
          ),
        ],
      );

  Widget _tip(IconData icon, String title, String description) => Container(
        margin: const EdgeInsets.only(bottom: 12),
        decoration: BoxDecoration(
          color: AppColors.white,
          borderRadius: BorderRadius.circular(15),
        ),
        child: ListTile(
          leading: CircleAvatar(
            backgroundColor: AppColors.orange.withValues(alpha: 0.12),
            child: Icon(icon, color: AppColors.orange),
          ),
          title: Text(
            title,
            style: const TextStyle(
              color: AppColors.navy,
              fontWeight: FontWeight.w600,
            ),
          ),
          subtitle: Padding(
            padding: const EdgeInsets.only(top: 4),
            child: Text(description),
          ),
        ),
      );

  Widget _settingsTab() => FutureBuilder<Map<String, dynamic>>(
        future: _profile,
        builder: (context, snapshot) {
          final user = snapshot.data?['user'] as Map<String, dynamic>?;
          return ListView(
            padding: const EdgeInsets.all(20),
            children: [
              Text(
                context.tr('Settings'),
                style: const TextStyle(
                  color: AppColors.navy,
                  fontSize: 21,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 16),
              _whitePanel(
                title: context.tr('Account'),
                child: ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: const CircleAvatar(
                    backgroundColor: AppColors.navy,
                    child: Icon(Icons.person, color: AppColors.white),
                  ),
                  title: Text(
                      user?['name']?.toString() ?? context.tr('MataKo user')),
                  subtitle: Text(user?['email']?.toString() ?? ''),
                ),
              ),
              const SizedBox(height: 14),
              _whitePanel(
                title: context.tr('Eye Care Settings'),
                child: SwitchListTile.adaptive(
                  contentPadding: EdgeInsets.zero,
                  activeThumbColor: AppColors.orange,
                  title: Text(context.tr('20-20-20 break reminder')),
                  subtitle: Text(context.tr('Remind me while the app is open')),
                  value: _remindersOn,
                  onChanged: _toggleReminder,
                ),
              ),
              const SizedBox(height: 14),
              _whitePanel(
                title: context.tr('Legal'),
                child: Column(
                  children: [
                    ListTile(
                      contentPadding: EdgeInsets.zero,
                      leading: const Icon(Icons.description_outlined),
                      title: Text(context.tr('Terms of Service')),
                      trailing: const Icon(Icons.chevron_right),
                      onTap: () => _openLegal(0),
                    ),
                    ListTile(
                      contentPadding: EdgeInsets.zero,
                      leading: const Icon(Icons.privacy_tip_outlined),
                      title: Text(context.tr('Privacy Policy')),
                      trailing: const Icon(Icons.chevron_right),
                      onTap: () => _openLegal(1),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 18),
              OutlinedButton.icon(
                onPressed: _logout,
                icon: const Icon(Icons.logout),
                label: Text(context.tr('Log out')),
              ),
            ],
          );
        },
      );
}
