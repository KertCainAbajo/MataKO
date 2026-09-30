import 'package:flutter/material.dart';

import '../l10n/app_localizations.dart';
import '../l10n/login_text.dart';
import '../services/api_service.dart';
import '../theme/app_colors.dart';
import 'home_screen.dart';
import 'register_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({
    super.key,
    this.language = 'English',
    this.initialRole = 'student',
  });

  final String language;
  final String initialRole;

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _email = TextEditingController();
  final _password = TextEditingController();
  final _api = ApiService();
  bool _loading = false;
  bool _passwordVisible = false;
  bool _rememberMe = false;

  LoginText get _text => LoginText.forLanguage(widget.language);

  Future<void> _login() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _loading = true);
    try {
      await _api.login(_email.text.trim(), _password.text);
      if (!mounted) return;
      Navigator.pushAndRemoveUntil(
        context,
        MaterialPageRoute<void>(builder: (_) => const HomeScreen()),
        (_) => false,
      );
    } catch (error) {
      _showMessage(
          context.tr(error.toString().replaceFirst('Exception: ', '')));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _showMessage(String message) {
    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(SnackBar(content: Text(message)));
  }

  void _signUp() {
    Navigator.of(context).push(
      MaterialPageRoute<void>(
        builder: (_) => RegisterScreen(initialRole: widget.initialRole),
      ),
    );
  }

  @override
  void dispose() {
    _email.dispose();
    _password.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => Scaffold(
        backgroundColor: AppColors.warmWhite,
        body: SafeArea(
          child: SingleChildScrollView(
            padding: const EdgeInsets.fromLTRB(20, 8, 20, 20),
            child: Center(
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 440),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Align(
                      alignment: Alignment.centerLeft,
                      child: IconButton(
                        tooltip: context.tr('Back'),
                        onPressed: () => Navigator.of(context).maybePop(),
                        icon: const Icon(Icons.arrow_back),
                        color: AppColors.navy,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Image.asset(
                      'assets/images/atam.png',
                      width: 170,
                      height: 54,
                      fit: BoxFit.contain,
                    ),
                    const SizedBox(height: 3),
                    Text(
                      _text.tagline,
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        color: Color(0xFF777777),
                        fontSize: 13,
                      ),
                    ),
                    const SizedBox(height: 34),
                    Text(
                      _text.subtitle,
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        color: Color(0xFF526174),
                        fontSize: 14,
                      ),
                    ),
                    const SizedBox(height: 20),
                    Container(
                      padding: const EdgeInsets.all(24),
                      decoration: BoxDecoration(
                        color: AppColors.white,
                        borderRadius: BorderRadius.circular(20),
                        boxShadow: [
                          BoxShadow(
                            color: AppColors.navy.withValues(alpha: 0.12),
                            blurRadius: 12,
                            offset: const Offset(0, 5),
                          ),
                        ],
                      ),
                      child: Form(
                        key: _formKey,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            _fieldLabel(_text.emailLabel),
                            const SizedBox(height: 8),
                            TextFormField(
                              controller: _email,
                              keyboardType: TextInputType.emailAddress,
                              decoration: _inputDecoration(
                                hint: _text.emailHint,
                                icon: Icons.mail_outline,
                              ),
                              validator: (value) =>
                                  value == null || !value.contains('@')
                                      ? _text.invalidEmail
                                      : null,
                            ),
                            const SizedBox(height: 22),
                            _fieldLabel(_text.passwordLabel),
                            const SizedBox(height: 8),
                            TextFormField(
                              controller: _password,
                              obscureText: !_passwordVisible,
                              decoration: _inputDecoration(
                                hint: _text.passwordHint,
                                icon: Icons.lock_outline,
                                suffix: IconButton(
                                  tooltip: _passwordVisible
                                      ? context.tr('Hide password')
                                      : context.tr('Show password'),
                                  onPressed: () => setState(() {
                                    _passwordVisible = !_passwordVisible;
                                  }),
                                  icon: Icon(
                                    _passwordVisible
                                        ? Icons.visibility_off_outlined
                                        : Icons.visibility_outlined,
                                    color: AppColors.orange,
                                  ),
                                ),
                              ),
                              validator: (value) =>
                                  value == null || value.isEmpty
                                      ? _text.passwordRequired
                                      : null,
                            ),
                            const SizedBox(height: 10),
                            Wrap(
                              alignment: WrapAlignment.spaceBetween,
                              crossAxisAlignment: WrapCrossAlignment.center,
                              children: [
                                Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Checkbox(
                                      value: _rememberMe,
                                      activeColor: AppColors.orange,
                                      visualDensity: VisualDensity.compact,
                                      onChanged: (value) => setState(
                                        () => _rememberMe = value ?? false,
                                      ),
                                    ),
                                    Text(
                                      _text.rememberMe,
                                      style: const TextStyle(fontSize: 12),
                                    ),
                                  ],
                                ),
                                TextButton(
                                  onPressed: () => _showMessage(
                                    _text.forgotUnavailable,
                                  ),
                                  style: TextButton.styleFrom(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 4,
                                    ),
                                    foregroundColor: AppColors.orange,
                                  ),
                                  child: Text(
                                    _text.forgotPassword,
                                    style: const TextStyle(fontSize: 12),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            SizedBox(
                              height: 48,
                              child: ElevatedButton(
                                onPressed: _loading ? null : _login,
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: AppColors.orange,
                                  foregroundColor: AppColors.white,
                                  elevation: 3,
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                ),
                                child: Text(
                                  _loading
                                      ? context.tr('Signing in…')
                                      : _text.signIn,
                                  style: const TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(height: 24),
                            Row(
                              children: [
                                const Expanded(child: Divider()),
                                Padding(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 12,
                                  ),
                                  child: Text(
                                    _text.orContinueWith,
                                    style: const TextStyle(
                                      color: Color(0xFF758092),
                                      fontSize: 12,
                                    ),
                                  ),
                                ),
                                const Expanded(child: Divider()),
                              ],
                            ),
                            const SizedBox(height: 18),
                            _socialButton(
                              icon: Icons.g_mobiledata,
                              label: _text.continueWithGoogle,
                            ),
                            const SizedBox(height: 10),
                            _socialButton(
                              icon: Icons.apple,
                              label: _text.continueWithApple,
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 22),
                    Wrap(
                      alignment: WrapAlignment.center,
                      crossAxisAlignment: WrapCrossAlignment.center,
                      children: [
                        Text(
                          _text.noAccount,
                          style: const TextStyle(
                            color: Color(0xFF526174),
                            fontSize: 12,
                          ),
                        ),
                        TextButton(
                          onPressed: _signUp,
                          style: TextButton.styleFrom(
                            foregroundColor: AppColors.orange,
                            padding: const EdgeInsets.symmetric(horizontal: 4),
                          ),
                          child: Text(
                            _text.signUp,
                            style: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      );

  Widget _fieldLabel(String text) => Text(
        text,
        style: const TextStyle(
          color: AppColors.navy,
          fontSize: 12,
          fontWeight: FontWeight.w500,
        ),
      );

  InputDecoration _inputDecoration({
    required String hint,
    required IconData icon,
    Widget? suffix,
  }) =>
      InputDecoration(
        hintText: hint,
        hintStyle: const TextStyle(color: Color(0xFFA6A8B6), fontSize: 14),
        prefixIcon: Icon(icon, color: AppColors.orange, size: 20),
        suffixIcon: suffix,
        filled: true,
        fillColor: AppColors.white,
        contentPadding: const EdgeInsets.symmetric(vertical: 14),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(13),
          borderSide: const BorderSide(color: AppColors.navy, width: 0.8),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(13),
          borderSide: const BorderSide(color: AppColors.orange, width: 1.5),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(13),
          borderSide: const BorderSide(color: Colors.red),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(13),
          borderSide: const BorderSide(color: Colors.red, width: 1.5),
        ),
      );

  Widget _socialButton({required IconData icon, required String label}) =>
      SizedBox(
        height: 46,
        child: OutlinedButton.icon(
          onPressed: () => _showMessage(_text.socialUnavailable),
          icon: Icon(icon, color: AppColors.orange, size: 20),
          label: Text(
            label,
            style: const TextStyle(color: AppColors.navy, fontSize: 13),
          ),
          style: OutlinedButton.styleFrom(
            side: const BorderSide(color: Color(0xFFE0E3E8)),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(13),
            ),
          ),
        ),
      );
}
