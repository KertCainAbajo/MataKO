/// Login page labels for the languages offered during onboarding.
class LoginText {
  const LoginText({
    required this.tagline,
    required this.subtitle,
    required this.emailLabel,
    required this.emailHint,
    required this.invalidEmail,
    required this.passwordLabel,
    required this.passwordHint,
    required this.passwordRequired,
    required this.rememberMe,
    required this.forgotPassword,
    required this.signIn,
    required this.signingIn,
    required this.orContinueWith,
    required this.continueWithGoogle,
    required this.continueWithApple,
    required this.noAccount,
    required this.signUp,
    required this.forgotUnavailable,
    required this.socialUnavailable,
  });

  final String tagline;
  final String subtitle;
  final String emailLabel;
  final String emailHint;
  final String invalidEmail;
  final String passwordLabel;
  final String passwordHint;
  final String passwordRequired;
  final String rememberMe;
  final String forgotPassword;
  final String signIn;
  final String signingIn;
  final String orContinueWith;
  final String continueWithGoogle;
  final String continueWithApple;
  final String noAccount;
  final String signUp;
  final String forgotUnavailable;
  final String socialUnavailable;

  static LoginText forLanguage(String language) {
    switch (language) {
      case 'Filipino':
        return const LoginText(
          tagline: 'IYONG PANINGIN, IYONG LAKAS.',
          subtitle: 'Mag-sign in sa iyong account para magpatuloy',
          emailLabel: 'Email Address',
          emailHint: 'Ilagay ang iyong email',
          invalidEmail: 'Maglagay ng wastong email address',
          passwordLabel: 'Password',
          passwordHint: 'Ilagay ang iyong password',
          passwordRequired: 'Ilagay ang iyong password',
          rememberMe: 'Tandaan ako',
          forgotPassword: 'Nakalimutan ang password?',
          signIn: 'Mag-sign in',
          signingIn: 'Nagla-login…',
          orContinueWith: 'O magpatuloy gamit ang',
          continueWithGoogle: 'Magpatuloy gamit ang Google',
          continueWithApple: 'Magpatuloy gamit ang Apple',
          noAccount: 'Wala ka pang account?',
          signUp: 'Mag-sign up',
          forgotUnavailable: 'Hindi pa available ang pag-reset ng password.',
          socialUnavailable: 'Hindi pa available ang social sign-in.',
        );
      case 'Cebuano':
        return const LoginText(
          tagline: 'IMONG PANAN-AW, IMONG KUSOG.',
          subtitle: 'Pag-sign in sa imong account aron mopadayon',
          emailLabel: 'Email Address',
          emailHint: 'Ibutang ang imong email',
          invalidEmail: 'Ibutang ang hustong email address',
          passwordLabel: 'Password',
          passwordHint: 'Ibutang ang imong password',
          passwordRequired: 'Ibutang ang imong password',
          rememberMe: 'Hinumdomi ko',
          forgotPassword: 'Nakalimot sa password?',
          signIn: 'Pag-sign in',
          signingIn: 'Nag-sign in…',
          orContinueWith: 'O ipadayon gamit ang',
          continueWithGoogle: 'Ipadayon gamit ang Google',
          continueWithApple: 'Ipadayon gamit ang Apple',
          noAccount: 'Wala pa kay account?',
          signUp: 'Pag-sign up',
          forgotUnavailable: 'Dili pa mahimo ang pag-reset sa password.',
          socialUnavailable: 'Dili pa mahimo ang social sign-in.',
        );
      default:
        return const LoginText(
          tagline: 'YOUR VISION, YOUR POWER.',
          subtitle: 'Sign in to your account to continue',
          emailLabel: 'Email Address',
          emailHint: 'Enter your email',
          invalidEmail: 'Enter a valid email address',
          passwordLabel: 'Password',
          passwordHint: 'Enter your password',
          passwordRequired: 'Enter your password',
          rememberMe: 'Remember me',
          forgotPassword: 'Forgot Password?',
          signIn: 'Sign In',
          signingIn: 'Signing in…',
          orContinueWith: 'Or continue with',
          continueWithGoogle: 'Continue with Google',
          continueWithApple: 'Continue with Apple',
          noAccount: "Don't have an account?",
          signUp: 'Sign Up',
          forgotUnavailable: 'Password reset is not available yet.',
          socialUnavailable: 'Social sign-in is not available yet.',
        );
    }
  }
}
