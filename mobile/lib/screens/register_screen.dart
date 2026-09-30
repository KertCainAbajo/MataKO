import 'package:flutter/material.dart';

import '../l10n/app_localizations.dart';
import '../services/api_service.dart';
import '../theme/app_colors.dart';
import 'terms_privacy_screen.dart';
import 'welcome_new_user_screen.dart';

/// Account creation form. The role comes from the earlier profile selection.
class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key, this.initialRole = 'student'});

  final String initialRole;

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _phone = TextEditingController();
  final _age = TextEditingController();
  final _password = TextEditingController();
  final _confirmPassword = TextEditingController();
  final _api = ApiService();

  late String _role;
  bool _loading = false;
  bool _passwordVisible = false;
  bool _confirmPasswordVisible = false;
  bool _agreedToPolicies = false;

  @override
  void initState() {
    super.initState();
    _role = widget.initialRole;
  }

  Future<void> _register() async {
    if (!_formKey.currentState!.validate()) return;
    if (!_agreedToPolicies) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content:
              Text(context.tr('Please agree to the Terms and Privacy Policy.')),
        ),
      );
      return;
    }

    setState(() => _loading = true);
    try {
      await _api.register(
        name: _name.text.trim(),
        email: _email.text.trim(),
        phone: _phone.text.trim(),
        password: _password.text,
        passwordConfirmation: _confirmPassword.text,
        age: int.parse(_age.text),
        role: _role,
      );
      if (!mounted) return;
      Navigator.pushAndRemoveUntil(
        context,
        MaterialPageRoute<void>(builder: (_) => const WelcomeNewUserScreen()),
        (_) => false,
      );
    } catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
                context.tr(error.toString().replaceFirst('Exception: ', ''))),
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _openLegal(int initialTabIndex) async {
    final accepted = await Navigator.of(context).push<bool>(
      MaterialPageRoute<bool>(
        builder: (_) => TermsPrivacyScreen(initialTabIndex: initialTabIndex),
      ),
    );
    if (accepted == true && mounted) {
      setState(() => _agreedToPolicies = true);
    }
  }

  @override
  void dispose() {
    _name.dispose();
    _email.dispose();
    _phone.dispose();
    _age.dispose();
    _password.dispose();
    _confirmPassword.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => Scaffold(
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
                      tooltip: context.tr('Back'),
                      onPressed: () => Navigator.of(context).pop(),
                      icon: const Icon(Icons.arrow_back),
                      color: AppColors.navy,
                    ),
                    Expanded(
                      child: Text(
                        context.tr('Sign Up'),
                        textAlign: TextAlign.center,
                        style: const TextStyle(
                          color: Color(0xFF111827),
                          fontSize: 17,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                    const SizedBox(width: 48),
                  ],
                ),
              ),
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.fromLTRB(22, 18, 22, 24),
                  child: Center(
                    child: ConstrainedBox(
                      constraints: const BoxConstraints(maxWidth: 440),
                      child: Form(
                        key: _formKey,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            Center(
                              child: Image.asset(
                                'assets/images/atam.png',
                                width: 145,
                                height: 48,
                                fit: BoxFit.contain,
                              ),
                            ),
                            const SizedBox(height: 12),
                            Text(
                              context.tr('Create Account'),
                              textAlign: TextAlign.center,
                              style: const TextStyle(
                                color: AppColors.navy,
                                fontSize: 21,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              context
                                  .tr('Join us and start your journey today'),
                              textAlign: TextAlign.center,
                              style: const TextStyle(
                                color: AppColors.navy,
                                fontSize: 13,
                              ),
                            ),
                            const SizedBox(height: 28),
                            _label(context.tr('Full Name')),
                            const SizedBox(height: 7),
                            _textField(
                              controller: _name,
                              hint: context.tr('Enter your full name'),
                              icon: Icons.person,
                              validator: (value) =>
                                  value == null || value.trim().isEmpty
                                      ? context.tr('Enter your full name')
                                      : null,
                            ),
                            const SizedBox(height: 14),
                            _label(context.tr('Email Address')),
                            const SizedBox(height: 7),
                            _textField(
                              controller: _email,
                              hint: context.tr('Enter your email'),
                              icon: Icons.mail,
                              keyboardType: TextInputType.emailAddress,
                              validator: (value) => value == null ||
                                      !value.contains('@')
                                  ? context.tr('Enter a valid email address')
                                  : null,
                            ),
                            const SizedBox(height: 14),
                            _label(context.tr('Phone Number')),
                            const SizedBox(height: 7),
                            _textField(
                              controller: _phone,
                              hint: context.tr('Enter your phone number'),
                              icon: Icons.phone,
                              keyboardType: TextInputType.phone,
                              validator: (value) =>
                                  value == null || value.trim().isEmpty
                                      ? context.tr('Enter your phone number')
                                      : null,
                            ),
                            const SizedBox(height: 14),
                            _label(context.tr('Age')),
                            const SizedBox(height: 7),
                            _textField(
                              controller: _age,
                              hint: context.tr('Enter your age'),
                              icon: Icons.cake_outlined,
                              keyboardType: TextInputType.number,
                              validator: (value) {
                                final age = int.tryParse(value ?? '');
                                return age == null || age < 18 || age > 120
                                    ? context.tr(
                                        'You must be 18 or older to sign up')
                                    : null;
                              },
                            ),
                            const SizedBox(height: 14),
                            _label(context.tr('Password')),
                            const SizedBox(height: 7),
                            _textField(
                              controller: _password,
                              hint: context.tr('Create a password'),
                              icon: Icons.lock,
                              obscureText: !_passwordVisible,
                              suffix: IconButton(
                                onPressed: () => setState(() {
                                  _passwordVisible = !_passwordVisible;
                                }),
                                icon: Icon(
                                  _passwordVisible
                                      ? Icons.visibility_off
                                      : Icons.visibility,
                                  color: AppColors.orange,
                                  size: 20,
                                ),
                              ),
                              validator: (value) =>
                                  value == null || value.length < 8
                                      ? context.tr('Use at least 8 characters')
                                      : null,
                            ),
                            const SizedBox(height: 14),
                            _label(context.tr('Confirm Password')),
                            const SizedBox(height: 7),
                            _textField(
                              controller: _confirmPassword,
                              hint: context.tr('Confirm your password'),
                              icon: Icons.lock,
                              obscureText: !_confirmPasswordVisible,
                              suffix: IconButton(
                                onPressed: () => setState(() {
                                  _confirmPasswordVisible =
                                      !_confirmPasswordVisible;
                                }),
                                icon: Icon(
                                  _confirmPasswordVisible
                                      ? Icons.visibility_off
                                      : Icons.visibility,
                                  color: AppColors.orange,
                                  size: 20,
                                ),
                              ),
                              validator: (value) => value != _password.text
                                  ? context.tr('Passwords do not match')
                                  : null,
                            ),
                            const SizedBox(height: 12),
                            Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                SizedBox(
                                  width: 24,
                                  height: 24,
                                  child: Checkbox(
                                    value: _agreedToPolicies,
                                    activeColor: AppColors.orange,
                                    onChanged: (value) => setState(
                                      () => _agreedToPolicies = value ?? false,
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Wrap(
                                    children: [
                                      Text(
                                        context.tr('I agree to the '),
                                        style: const TextStyle(
                                          color: Color(0xFF647084),
                                          fontSize: 11,
                                        ),
                                      ),
                                      _legalLink(
                                          context.tr('Terms of Service'), 0),
                                      Text(
                                        context.tr(' and '),
                                        style: const TextStyle(
                                          color: Color(0xFF647084),
                                          fontSize: 11,
                                        ),
                                      ),
                                      _legalLink(
                                          context.tr('Privacy Policy'), 1),
                                      const Text(
                                        '.',
                                        style: TextStyle(fontSize: 11),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 24),
                            SizedBox(
                              height: 48,
                              child: ElevatedButton(
                                onPressed: _loading ? null : _register,
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: AppColors.orange,
                                  foregroundColor: AppColors.white,
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                ),
                                child: Text(
                                  _loading
                                      ? context.tr('Creating account…')
                                      : context.tr('Create Account'),
                                  style: const TextStyle(
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(height: 22),
                            Row(
                              children: [
                                const Expanded(child: Divider()),
                                Padding(
                                  padding: const EdgeInsets.symmetric(
                                      horizontal: 12),
                                  child: Text(
                                    context.tr('or continue with'),
                                    style: const TextStyle(
                                      color: Color(0xFF858B99),
                                      fontSize: 12,
                                    ),
                                  ),
                                ),
                                const Expanded(child: Divider()),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      );

  Widget _label(String text) => Text(
        text,
        style: const TextStyle(
          color: Color(0xFF344054),
          fontSize: 12,
          fontWeight: FontWeight.w500,
        ),
      );

  Widget _textField({
    required TextEditingController controller,
    required String hint,
    required IconData icon,
    required String? Function(String?) validator,
    TextInputType? keyboardType,
    bool obscureText = false,
    Widget? suffix,
  }) =>
      TextFormField(
        controller: controller,
        keyboardType: keyboardType,
        obscureText: obscureText,
        validator: validator,
        decoration: InputDecoration(
          hintText: hint,
          hintStyle: const TextStyle(color: Color(0xFFA7ADBC), fontSize: 13),
          prefixIcon: Icon(icon, color: AppColors.orange, size: 18),
          suffixIcon: suffix,
          filled: true,
          fillColor: AppColors.white,
          contentPadding: const EdgeInsets.symmetric(vertical: 12),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(7),
            borderSide: const BorderSide(color: AppColors.navy, width: 0.8),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(7),
            borderSide: const BorderSide(color: AppColors.orange, width: 1.5),
          ),
          errorBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(7),
            borderSide: const BorderSide(color: Colors.red),
          ),
          focusedErrorBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(7),
            borderSide: const BorderSide(color: Colors.red, width: 1.5),
          ),
        ),
      );

  Widget _legalLink(String text, int tabIndex) => TextButton(
        onPressed: () => _openLegal(tabIndex),
        style: TextButton.styleFrom(
          padding: EdgeInsets.zero,
          minimumSize: Size.zero,
          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
          foregroundColor: AppColors.orange,
        ),
        child: Text(
          text,
          style: const TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w500,
          ),
        ),
      );
}
