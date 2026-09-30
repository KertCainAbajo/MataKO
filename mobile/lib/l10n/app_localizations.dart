import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';

/// Lightweight app translations for English, Filipino, and Cebuano.
class AppLocalizations {
  const AppLocalizations(this.locale);

  final Locale locale;

  static AppLocalizations of(BuildContext context) =>
      Localizations.of<AppLocalizations>(context, AppLocalizations)!;

  String text(String english) =>
      _translations[locale.languageCode]?[english] ?? english;

  static const LocalizationsDelegate<AppLocalizations> delegate =
      _AppLocalizationsDelegate();

  static const Map<String, Map<String, String>> _translations = {
    'fil': {
      'Tap to continue': 'I-tap para magpatuloy',
      'Get Started': 'Magsimula',
      'Select Your Profile Type': 'Piliin ang Uri ng Iyong Profile',
      'This will help us tailor your experience.':
          'Tutulungan kami nitong iangkop ang iyong karanasan.',
      'Select Student': 'Piliin ang Estudyante',
      'Select Professional': 'Piliin ang Propesyonal',
      'Select a language you\'re\nmost comfortable with.':
          'Piliin ang wikang\npinakakomportable ka.',
      'Choose your language:': 'Piliin ang iyong wika:',
      'Select a language': 'Pumili ng wika',
      'Proceed': 'Magpatuloy',
      'Skip for now': 'Laktawan muna',
      'Back': 'Bumalik',
      'YOUR VISION, YOUR POWER.': 'IYONG PANINGIN, IYONG LAKAS.',
      'Sign in to your account to continue':
          'Mag-sign in sa iyong account para magpatuloy',
      'Email Address': 'Email Address',
      'Enter your email': 'Ilagay ang iyong email',
      'Enter a valid email address': 'Maglagay ng wastong email address',
      'Password': 'Password',
      'Enter your password': 'Ilagay ang iyong password',
      'Remember me': 'Tandaan ako',
      'Forgot Password?': 'Nakalimutan ang password?',
      'Sign In': 'Mag-sign in',
      'Signing in…': 'Nagla-login…',
      'Or continue with': 'O magpatuloy gamit ang',
      'Continue with Google': 'Magpatuloy gamit ang Google',
      'Continue with Apple': 'Magpatuloy gamit ang Apple',
      'Don\'t have an account?': 'Wala ka pang account?',
      'Sign Up': 'Mag-sign up',
      'Password reset is not available yet.':
          'Hindi pa available ang pag-reset ng password.',
      'Social sign-in is not available yet.':
          'Hindi pa available ang social sign-in.',
      'Hide password': 'Itago ang password',
      'Show password': 'Ipakita ang password',
      'Create Account': 'Gumawa ng Account',
      'Join us and start your journey today':
          'Sumali at simulan ang iyong paglalakbay ngayon',
      'Full Name': 'Buong Pangalan',
      'Enter your full name': 'Ilagay ang iyong buong pangalan',
      'Phone Number': 'Numero ng Telepono',
      'Enter your phone number': 'Ilagay ang iyong numero ng telepono',
      'Age': 'Edad',
      'Enter your age': 'Ilagay ang iyong edad',
      'You must be 18 or older to sign up':
          'Dapat ay 18 taong gulang pataas upang mag-sign up',
      'Create a password': 'Gumawa ng password',
      'Use at least 8 characters': 'Gumamit ng hindi bababa sa 8 character',
      'Confirm Password': 'Kumpirmahin ang Password',
      'Confirm your password': 'Kumpirmahin ang iyong password',
      'Passwords do not match': 'Hindi magkatugma ang mga password',
      'I agree to the ': 'Sumasang-ayon ako sa ',
      ' and ': ' at sa ',
      'Terms of Service': 'Mga Tuntunin ng Serbisyo',
      'Privacy Policy': 'Patakaran sa Privacy',
      'Please agree to the Terms and Privacy Policy.':
          'Sumang-ayon muna sa Mga Tuntunin at Patakaran sa Privacy.',
      'Creating account…': 'Ginagawa ang account…',
      'or continue with': 'o magpatuloy gamit ang',
      'Hello,\nwelcome to': 'Kumusta,\nmaligayang pagdating sa',
      'Your personal companion in\nprotecting your eyes from\n':
          'Ang iyong kasama sa\npangangalaga sa iyong mga mata laban sa\n',
      'Digital Eye Strain.': 'Digital Eye Strain.',
      'Would you like a ': 'Gusto mo ba ng ',
      'quick tour': 'maikling tour',
      ' of the app features, or go directly to your eye care dashboard?':
          ' ng mga feature ng app, o dumiretso sa dashboard ng pangangalaga sa mata?',
      'Take a Tour': 'Mag-tour',
      'Go to Home': 'Pumunta sa Home',
      'Quick Tour': 'Maikling Tour',
      'Skip': 'Laktawan',
      'Check how your eyes feel': 'Tingnan ang pakiramdam ng iyong mga mata',
      'Answer a few questions about common digital eye strain symptoms.':
          'Sagutin ang ilang tanong tungkol sa karaniwang sintomas ng pagkapagod ng mata mula sa screen.',
      'Understand your result': 'Unawain ang iyong resulta',
      'See your risk level and get simple recommendations based on your answers.':
          'Tingnan ang antas ng panganib at mga mungkahing batay sa iyong mga sagot.',
      'Build healthier screen habits':
          'Bumuo ng mas mabuting gawi sa paggamit ng screen',
      'Use your history and 20-20-20 break reminders to care for your eyes.':
          'Gamitin ang iyong history at 20-20-20 na paalala para pangalagaan ang iyong mga mata.',
      'Next': 'Susunod',
      'Self-assessment': 'Pagsusuri sa sarili',
      'Self-Assessment': 'Pagsusuri sa Sarili',
      'Over the past week, how often have you experienced each symptom?':
          'Sa nakaraang linggo, gaano kadalas mong naranasan ang bawat sintomas?',
      'Eye pain': 'Pananakit ng mata',
      'Dry eyes': 'Tuyong mga mata',
      'Blurred vision': 'Malabong paningin',
      'Headache': 'Sakit ng ulo',
      'Eye fatigue': 'Pagkapagod ng mata',
      'None': 'Hindi kailanman',
      'Sometimes': 'Paminsan-minsan',
      'Often': 'Madalas',
      'Always': 'Palagi',
      'Saving…': 'Sine-save…',
      'See my result': 'Tingnan ang resulta',
      'Your result': 'Iyong Resulta',
      'Digital eye strain risk · {score} of 15 points':
          'Panganib ng pagkapagod ng mata · {score} sa 15 puntos',
      'Suggestions for you': 'Mga mungkahi para sa iyo',
      'This screening result is not a diagnosis. If symptoms persist or concern you, consider speaking with an eye care professional.':
          'Hindi ito medikal na diagnosis. Kung nagpapatuloy o ikinababahala mo ang mga sintomas, kumonsulta sa propesyonal sa pangangalaga ng mata.',
      'Back to dashboard': 'Bumalik sa dashboard',
      'Maintain good screen habits':
          'Panatilihin ang mabuting gawi sa paggamit ng screen',
      'Take occasional breaks': 'Magpahinga paminsan-minsan',
      'Follow the 20-20-20 rule': 'Sundin ang 20-20-20 na tuntunin',
      'Adjust screen brightness': 'Ayusin ang liwanag ng screen',
      'Blink more often': 'Mas madalas na kumurap',
      'Remember to blink fully while reading or working on a screen.':
          'Kumurap nang buo habang nagbabasa o nagtatrabaho sa screen.',
      'Every 20 minutes, look about 20 feet away for 20 seconds.':
          'Tuwing 20 minuto, tumingin sa bagay na 20 talampakan ang layo nang 20 segundo.',
      'Blink often': 'Madalas na kumurap',
      'Keep a strict break schedule':
          'Sundin ang regular na iskedyul ng pahinga',
      'Reduce screen time': 'Bawasan ang oras sa screen',
      'Improve your ergonomics': 'Ayusin ang iyong puwesto sa pagtatrabaho',
      'Consider consulting an eye specialist':
          'Kumonsulta sa espesyalista sa mata',
      'Hi, {name}!': 'Kumusta, {name}!',
      'Hi, User!': 'Kumusta, User!',
      'User': 'Gumagamit',
      'Date unavailable': 'Hindi available ang petsa',
      'The email has already been taken.': 'Nagamit na ang email na ito.',
      'The phone has already been taken.':
          'Nagamit na ang numero ng teleponong ito.',
      'The provided credentials are incorrect.':
          'Hindi tama ang email o password.',
      'Request failed. Please try again.':
          'Hindi nagtagumpay ang kahilingan. Subukan muli.',
      'Ready to take care of your eyes today?':
          'Handa ka na bang alagaan ang iyong mga mata ngayon?',
      'Start Self-Assessment': 'Simulan ang Pagsusuri sa Sarili',
      'Quick check-up for your eye health':
          'Mabilis na pagsusuri para sa kalusugan ng iyong mata',
      'View Care Tips': 'Tingnan ang Mga Tip sa Pangangalaga',
      'Learn how to protect your vision':
          'Alamin kung paano pangalagaan ang iyong paningin',
      'Set Screen Break Reminders': 'Magtakda ng Paalala sa Pagpapahinga',
      'Schedule healthy breaks from screens':
          'Mag-iskedyul ng pahinga mula sa screen',
      'Eye Care Settings': 'Mga Setting sa Pangangalaga ng Mata',
      'Manage reminders and your account':
          'Pamahalaan ang mga paalala at account',
      'This self-assessment is for general guidance and is not a medical diagnosis.':
          'Pangkalahatang gabay lamang ang pagsusuring ito at hindi medikal na diagnosis.',
      'Today\'s Progress': 'Progreso Ngayon',
      'Assessments today': 'Mga pagsusuri ngayon',
      'Break reminders': 'Mga paalala sa pahinga',
      'Today\'s Eye Tip': 'Tip sa Pangangalaga ng Mata Ngayon',
      'Remember to blink more often while using your screen. The 20-20-20 rule can help reduce eye strain.':
          'Madalas na kumurap habang gumagamit ng screen. Makakatulong ang 20-20-20 na tuntunin upang mabawasan ang pagkapagod ng mata.',
      'Recent Activity': 'Kamakailang Aktibidad',
      'Could not load your activity.': 'Hindi ma-load ang iyong aktibidad.',
      'No assessments yet. Complete a self-assessment to see your activity here.':
          'Wala pang pagsusuri. Kumpletuhin ang pagsusuri sa sarili upang makita rito ang iyong aktibidad.',
      'Retake': 'Ulitin',
      'Your Progress': 'Iyong Progreso',
      'Review your recent eye strain assessments.':
          'Suriin ang iyong mga kamakailang pagsusuri sa pagkapagod ng mata.',
      'Assessment History': 'History ng Pagsusuri',
      'Your completed assessments will appear here.':
          'Lalabas dito ang mga natapos mong pagsusuri.',
      ' points': ' puntos',
      'Reload progress': 'I-reload ang progreso',
      'Eye Care': 'Pangangalaga ng Mata',
      'Small, regular breaks can help during screen use.':
          'Makakatulong ang maikli at regular na pahinga habang gumagamit ng screen.',
      '20-20-20 break reminder': 'Paalala sa 20-20-20 na pahinga',
      'Reminder repeats every 20 minutes while MataKo is open.':
          'Umuulit ang paalala kada 20 minuto habang bukas ang MataKo.',
      'Turn this on to receive an in-app break reminder.':
          'I-on ito para makatanggap ng paalala sa pahinga sa app.',
      'The 20-20-20 rule': 'Ang 20-20-20 na tuntunin',
      'Every 20 minutes, look at something about 20 feet away for at least 20 seconds.':
          'Tuwing 20 minuto, tumingin sa bagay na 20 talampakan ang layo nang hindi bababa sa 20 segundo.',
      'Care Tips': 'Mga Tip sa Pangangalaga',
      'Simple habits for more comfortable screen use.':
          'Mga simpleng gawi para mas komportable sa paggamit ng screen.',
      'Reduce glare': 'Bawasan ang silaw',
      'Adjust screen brightness and position lights to avoid reflections.':
          'Ayusin ang liwanag ng screen at ilaw upang maiwasan ang repleksyon.',
      'Set up your workspace': 'Ayusin ang iyong lugar ng trabaho',
      'Keep a comfortable viewing distance and sit with relaxed posture.':
          'Panatilihin ang komportableng layo sa screen at umupo nang maayos.',
      'Settings': 'Mga Setting',
      'Account': 'Account',
      'MataKo user': 'Gumagamit ng MataKo',
      'Remind me while the app is open':
          'Paalalahanan ako habang bukas ang app',
      'Legal': 'Legal na Impormasyon',
      'Log out': 'Mag-log out',
      'Back to Home': 'Bumalik sa Home',
      'Notifications': 'Mga Abiso',
      'Your profile and settings': 'Iyong profile at mga setting',
      'Home': 'Home',
      'Progress': 'Progreso',
      'Tips': 'Mga Tip',
      'Eye break: look 20 feet away for 20 seconds.':
          'Pahinga muna: tumingin sa bagay na 20 talampakan ang layo nang 20 segundo.',
      'Notification': 'Abiso',
      'Filter notifications': 'I-filter ang mga abiso',
      'All': 'Lahat',
      'Today': 'Ngayon',
      'Yesterday': 'Kahapon',
      'Screen Break Reminders': 'Mga Paalala sa Pagpapahinga',
      '👀 Time to rest your eyes! Look 30 feet away for 30 seconds.':
          '👀 Oras nang ipahinga ang iyong mga mata! Tumingin sa 30 talampakan ang layo nang 30 segundo.',
      'You\'ve been on screen for 1 hour. Take a short break and blink more!':
          'Isang oras ka nang nakatingin sa screen. Magpahinga sandali at kumurap nang mas madalas!',
      'More eye care tips': 'Higit pang tip sa pangangalaga ng mata',
      '📝 Haven\'t checked in today? Take your quick eye health check-up now.':
          '📝 Hindi ka pa nakakapagsuri ngayon? Gawin na ang mabilis na pagsusuri sa mata.',
      '🎉 Congratulations!': '🎉 Binabati ka namin!',
      'You\'ve taken 3 screen breaks today. Great job protecting your eyes!':
          'Nagpahinga ka nang 3 beses mula sa screen ngayon. Mahusay ang pangangalaga mo sa iyong mga mata!',
      'System Alert!': 'Abiso ng System!',
      '⚙️ Update your screen break settings for better tracking.':
          '⚙️ I-update ang mga setting ng pahinga para masubaybayan ito nang mabuti.',
      'No notifications to show.': 'Walang abisong ipapakita.',
      'I Agree and Continue': 'Sumasang-ayon ako at Magpatuloy',
      'Done': 'Tapos na',
      'Back to Sign Up': 'Bumalik sa Pag-sign up',
      '1. Acceptance of Terms': '1. Pagsang-ayon sa mga tuntunin',
      '2. Purpose of the Service': '2. Layunin ng Serbisyo',
      '3. User Responsibilities': '3. Mga Responsibilidad ng Gumagamit',
      '4. Health Disclaimer': '4. Paalala sa Kalusugan',
      '5. Content Usage': '5. Paggamit ng Nilalaman',
      '1. Information We Collect': '1. Impormasyong Kinokolekta Namin',
      '2. How We Use Your Data': '2. Paano Ginagamit ang Iyong Data',
      '3. Data Sharing': '3. Pagbabahagi ng Data',
      '4. Storage and Security': '4. Pag-iimbak at Seguridad',
      '5. Your Rights': '5. Iyong mga Karapatan',
      'Welcome to MataKo, your personal guide to managing Digital Eye Strain (DES). By using MataKo, you agree to these terms. Please read them carefully.':
          'Maligayang pagdating sa MataKo, ang iyong gabay sa pamamahala ng Digital Eye Strain (DES). Sa paggamit ng MataKo, sumasang-ayon ka sa mga tuntuning ito. Basahin itong mabuti.',
      'By accessing and using MataKo, you confirm that you are at least 18 years old and agree to be bound by these Terms of Service.':
          'Sa paggamit ng MataKo, kinukumpirma mong hindi bababa sa 18 taong gulang ka at sumasang-ayon ka sa Mga Tuntunin ng Serbisyo.',
      'MataKo helps adults and working professionals self-assess symptoms and manage Digital Eye Strain through self-evaluation tools, behavior strategies, reminders, and educational content.':
          'Tinutulungan ng MataKo ang mga nasa hustong gulang at propesyonal na suriin ang mga sintomas at pamahalaan ang Digital Eye Strain gamit ang mga pagsusuri, estratehiya, paalala, at materyal na pang-edukasyon.',
      'You are responsible for keeping your MataKo account confidential and using the app lawfully and respectfully. Assessments and feedback are informational only and do not replace medical advice.':
          'Responsibilidad mong panatilihing pribado ang iyong account at gamitin ang app nang naaayon sa batas at may paggalang. Gabay lamang ang mga pagsusuri at puna at hindi kapalit ng payong medikal.',
      'MataKo is not intended to diagnose, treat, or replace professional medical advice. If you have ongoing or severe symptoms, consult a licensed eye care professional.':
          'Hindi nilalayong mag-diagnose o gumamot ang MataKo at hindi ito kapalit ng payong medikal. Kung nagpapatuloy o malubha ang sintomas, kumonsulta sa lisensyadong propesyonal sa mata.',
      'MataKo content, including ergonomic suggestions and educational materials, is provided for personal informational use. Do not reproduce or redistribute it without permission.':
          'Para sa personal na kaalaman ang nilalaman ng MataKo, kabilang ang mga mungkahing ergonomiko at materyal na pang-edukasyon. Huwag itong kopyahin o ipamahagi nang walang pahintulot.',
      'At MataKo, we value your privacy and are committed to protecting your personal and health-related information.':
          'Pinahahalagahan ng MataKo ang iyong privacy at pinoprotektahan ang iyong personal at impormasyong pangkalusugan.',
      'Personal information: name, email address, phone number, and age. Health data: your self-assessment responses, including Digital Eye Strain symptoms. App usage data may include interactions with features, reminders, and educational tips.':
          'Personal na impormasyon: pangalan, email, numero ng telepono, at edad. Datos pangkalusugan: mga sagot sa pagsusuri sa sarili, kabilang ang mga sintomas ng Digital Eye Strain. Maaaring kasama sa datos ng paggamit ang mga feature, paalala, at tip na ginamit.',
      'We use information to provide assessment results, personalized care suggestions, reminders, and educational content, and to maintain and improve the app.':
          'Ginagamit namin ang impormasyon upang ibigay ang resulta ng pagsusuri, mga mungkahi, paalala, at materyal na pang-edukasyon, at upang panatilihin at pagbutihin ang app.',
      'MataKo does not sell your personal information. Data may be used in aggregated form to evaluate and improve the service. We do not share identifying health information without your consent.':
          'Hindi ibinebenta ng MataKo ang iyong personal na impormasyon. Maaaring gamitin ang pinagsama-samang datos upang suriin at pagbutihin ang serbisyo. Hindi ibinabahagi ang impormasyong pangkalusugan na makakakilala sa iyo nang walang pahintulot.',
      'Your information is stored in the database configured for the MataKo service. Access is limited to the service and its authorized operators. Security protections depend on the environment where the service is deployed.':
          'Iniimbak ang iyong impormasyon sa database ng serbisyo ng MataKo. Limitado ang access sa serbisyo at mga awtorisadong operator nito. Nakadepende ang mga proteksiyon sa seguridad sa kapaligirang pinaglalagyan ng serbisyo.',
      'You may request access to or correction of your account information. You may also request account deletion from the MataKo service administrator.':
          'Maaari kang humiling na makita o itama ang impormasyon ng iyong account. Maaari ka ring humiling sa tagapangasiwa ng MataKo na burahin ang iyong account.',
      'Welcome to MataKo': 'Maligayang pagdating sa MataKo',
      'The MataKo server did not respond. Start the Laravel backend and try again.':
          'Walang tugon ang server ng MataKo. Simulan ang Laravel backend at subukan muli.',
      'Cannot connect to the MataKo server. Start the Laravel backend and try again.':
          'Hindi makakonekta sa server ng MataKo. Simulan ang Laravel backend at subukan muli.',
    },
    'ceb': {
      'Tap to continue': 'Pindota aron mopadayon',
      'Get Started': 'Pagsugod',
      'Select Your Profile Type': 'Pilia ang Matang sa Imong Profile',
      'This will help us tailor your experience.':
          'Makatabang kini namo sa pagpahiangay sa imong kasinatian.',
      'Select Student': 'Pilia ang Estudyante',
      'Select Professional': 'Pilia ang Propesyonal',
      'Select a language you\'re\nmost comfortable with.':
          'Pilia ang pinakakomportable\nnimong pinulongan.',
      'Choose your language:': 'Pilia ang imong pinulongan:',
      'Select a language': 'Pilia ang pinulongan',
      'Proceed': 'Padayon',
      'Skip for now': 'Laktawi lang sa',
      'Back': 'Balik',
      'YOUR VISION, YOUR POWER.': 'IMONG PANAN-AW, IMONG KUSOG.',
      'Sign in to your account to continue':
          'Pag-sign in sa imong account aron mopadayon',
      'Email Address': 'Email Address',
      'Enter your email': 'Isulod ang imong email',
      'Enter a valid email address': 'Isulod ang hustong email address',
      'Password': 'Password',
      'Enter your password': 'Isulod ang imong password',
      'Remember me': 'Hinumdomi ko',
      'Forgot Password?': 'Nakalimot sa password?',
      'Sign In': 'Pag-sign in',
      'Signing in…': 'Nag-sign in…',
      'Or continue with': 'O ipadayon gamit ang',
      'Continue with Google': 'Ipadayon gamit ang Google',
      'Continue with Apple': 'Ipadayon gamit ang Apple',
      'Don\'t have an account?': 'Wala pa kay account?',
      'Sign Up': 'Pag-sign up',
      'Password reset is not available yet.':
          'Dili pa mahimo ang pag-reset sa password.',
      'Social sign-in is not available yet.':
          'Dili pa mahimo ang social sign-in.',
      'Hide password': 'Tagoa ang password',
      'Show password': 'Ipakita ang password',
      'Create Account': 'Paghimo og Account',
      'Join us and start your journey today':
          'Apil ug sugdi karon ang imong panaw',
      'Full Name': 'Tibuok Ngalan',
      'Enter your full name': 'Isulod ang imong tibuok ngalan',
      'Phone Number': 'Numero sa Telepono',
      'Enter your phone number': 'Isulod ang imong numero sa telepono',
      'Age': 'Edad',
      'Enter your age': 'Isulod ang imong edad',
      'You must be 18 or older to sign up':
          'Kinahanglan 18 anyos pataas aron maka-sign up',
      'Create a password': 'Paghimo og password',
      'Use at least 8 characters': 'Gamit og labing menos 8 ka karakter',
      'Confirm Password': 'Kumpirmaha ang Password',
      'Confirm your password': 'Kumpirmaha ang imong password',
      'Passwords do not match': 'Dili magkaparehas ang mga password',
      'I agree to the ': 'Miuyon ko sa ',
      ' and ': ' ug sa ',
      'Terms of Service': 'Mga Termino sa Serbisyo',
      'Privacy Policy': 'Patakaran sa Privacy',
      'Please agree to the Terms and Privacy Policy.':
          'Palihog sunda ang mga Termino ug Patakaran sa Privacy.',
      'Creating account…': 'Naghimo og account…',
      'or continue with': 'o ipadayon gamit ang',
      'Hello,\nwelcome to': 'Kumusta,\nmaayong pag-abot sa',
      'Your personal companion in\nprotecting your eyes from\n':
          'Imong kauban sa\npagpanalipod sa imong mga mata batok sa\n',
      'Digital Eye Strain.': 'Digital Eye Strain.',
      'Would you like a ': 'Gusto ka ba og ',
      'quick tour': 'mubo nga tour',
      ' of the app features, or go directly to your eye care dashboard?':
          ' sa mga feature sa app, o diretso sa dashboard sa pag-atiman sa mata?',
      'Take a Tour': 'Tan-awa ang Tour',
      'Go to Home': 'Adto sa Home',
      'Quick Tour': 'Mubo nga Tour',
      'Skip': 'Laktawi',
      'Check how your eyes feel': 'Susiha ang kahimtang sa imong mga mata',
      'Answer a few questions about common digital eye strain symptoms.':
          'Tubaga ang pipila ka pangutana bahin sa kasagarang sintomas sa kakapoy sa mata tungod sa screen.',
      'Understand your result': 'Sabta ang imong resulta',
      'See your risk level and get simple recommendations based on your answers.':
          'Tan-awa ang lebel sa risgo ug makadawat og mga sugyot base sa imong mga tubag.',
      'Build healthier screen habits':
          'Paghimo og mas maayong batasan sa paggamit sa screen',
      'Use your history and 20-20-20 break reminders to care for your eyes.':
          'Gamita ang imong history ug 20-20-20 nga pahinumdom aron maatiman ang imong mga mata.',
      'Next': 'Sunod',
      'Self-assessment': 'Pagsusi sa kaugalingon',
      'Self-Assessment': 'Pagsusi sa Kaugalingon',
      'Over the past week, how often have you experienced each symptom?':
          'Sa miaging semana, unsa ka kanunay nimo nasinati ang matag sintomas?',
      'Eye pain': 'Sakit sa mata',
      'Dry eyes': 'Uga nga mga mata',
      'Blurred vision': 'Hanap nga panan-aw',
      'Headache': 'Sakit sa ulo',
      'Eye fatigue': 'Kakapoy sa mata',
      'None': 'Wala',
      'Sometimes': 'Usahay',
      'Often': 'Kanunay',
      'Always': 'Kanunay gyud',
      'Saving…': 'Ginatipigan…',
      'See my result': 'Tan-awa ang akong resulta',
      'Your result': 'Imong Resulta',
      'Digital eye strain risk · {score} of 15 points':
          'Risgo sa kakapoy sa mata · {score} sa 15 ka puntos',
      'Suggestions for you': 'Mga sugyot para kanimo',
      'This screening result is not a diagnosis. If symptoms persist or concern you, consider speaking with an eye care professional.':
          'Dili kini medikal nga diagnosis. Kung magpadayon o mabalaka ka sa mga sintomas, pakigkonsulta sa propesyonal sa pag-atiman sa mata.',
      'Back to dashboard': 'Balik sa dashboard',
      'Maintain good screen habits':
          'Padayon sa maayong batasan sa paggamit sa screen',
      'Take occasional breaks': 'Panagsang pagpahulay',
      'Follow the 20-20-20 rule': 'Sunda ang 20-20-20 nga lagda',
      'Adjust screen brightness': 'Iangay ang kahayag sa screen',
      'Blink more often': 'Mas kanunay pagkidlap',
      'Remember to blink fully while reading or working on a screen.':
          'Kumidlap og maayo samtang nagbasa o nagtrabaho gamit ang screen.',
      'Every 20 minutes, look about 20 feet away for 20 seconds.':
          'Matag 20 minutos, tan-awa ang butang nga mga 20 ka tiil ang gilay-on sulod sa 20 segundos.',
      'Blink often': 'Kanunay pagkidlap',
      'Keep a strict break schedule':
          'Sunda ang regular nga iskedyul sa pagpahulay',
      'Reduce screen time': 'Pakunhora ang oras sa screen',
      'Improve your ergonomics': 'Ipaayo ang kahikayan sa imong trabaho',
      'Consider consulting an eye specialist':
          'Pakigkonsulta sa espesyalista sa mata',
      'Hi, {name}!': 'Kumusta, {name}!',
      'Hi, User!': 'Kumusta, User!',
      'User': 'Tiggamit',
      'Date unavailable': 'Wala magamit nga petsa',
      'The email has already been taken.': 'Nagamit na kini nga email.',
      'The phone has already been taken.':
          'Nagamit na kini nga numero sa telepono.',
      'The provided credentials are incorrect.': 'Sayop ang email o password.',
      'Request failed. Please try again.':
          'Napakyas ang hangyo. Palihog sulayi pag-usab.',
      'Ready to take care of your eyes today?':
          'Andam ka na ba mo-atiman sa imong mga mata karon?',
      'Start Self-Assessment': 'Sugdi ang Pagsusi sa Kaugalingon',
      'Quick check-up for your eye health':
          'Dali nga pagsusi sa kahimsog sa imong mata',
      'View Care Tips': 'Tan-awa ang mga Tip sa Pag-atiman',
      'Learn how to protect your vision':
          'Hibal-i unsaon pagpanalipod sa imong panan-aw',
      'Set Screen Break Reminders': 'Pagbutang og Pahinumdom sa Pagpahulay',
      'Schedule healthy breaks from screens':
          'Pag-iskedyul og pahulay gikan sa screen',
      'Eye Care Settings': 'Mga Setting sa Pag-atiman sa Mata',
      'Manage reminders and your account':
          'Pagdumala sa mga pahinumdom ug account',
      'This self-assessment is for general guidance and is not a medical diagnosis.':
          'Kinatibuk-ang giya lamang kini ug dili medikal nga diagnosis.',
      'Today\'s Progress': 'Progreso Karon',
      'Assessments today': 'Mga pagsusi karon',
      'Break reminders': 'Mga pahinumdom sa pagpahulay',
      'Today\'s Eye Tip': 'Tip sa Mata Karon',
      'Remember to blink more often while using your screen. The 20-20-20 rule can help reduce eye strain.':
          'Hinumdomi ang pagkidlap samtang naggamit og screen. Makatabang ang 20-20-20 nga lagda sa pagpakunhod sa kakapoy sa mata.',
      'Recent Activity': 'Bag-ong Kalihokan',
      'Could not load your activity.': 'Dili ma-load ang imong kalihokan.',
      'No assessments yet. Complete a self-assessment to see your activity here.':
          'Wala pay pagsusi. Kompletoha ang pagsusi aron makita dinhi ang imong kalihokan.',
      'Retake': 'Usba',
      'Your Progress': 'Imong Progreso',
      'Review your recent eye strain assessments.':
          'Tan-awa ang imong bag-ong mga pagsusi sa kakapoy sa mata.',
      'Assessment History': 'Kasaysayan sa Pagsusi',
      'Your completed assessments will appear here.':
          'Dinhi makita ang nahuman nimong mga pagsusi.',
      ' points': ' puntos',
      'Reload progress': 'I-reload ang progreso',
      'Eye Care': 'Pag-atiman sa Mata',
      'Small, regular breaks can help during screen use.':
          'Makatabang ang mubo ug kanunay nga pagpahulay samtang naggamit og screen.',
      '20-20-20 break reminder': 'Pahinumdom sa 20-20-20 nga pahulay',
      'Reminder repeats every 20 minutes while MataKo is open.':
          'Mosubli ang pahinumdom matag 20 minutos samtang bukas ang MataKo.',
      'Turn this on to receive an in-app break reminder.':
          'Ablihi kini aron makadawat og pahinumdom sa pagpahulay sa app.',
      'The 20-20-20 rule': 'Ang 20-20-20 nga lagda',
      'Every 20 minutes, look at something about 20 feet away for at least 20 seconds.':
          'Matag 20 minutos, tan-awa ang butang nga mga 20 ka tiil ang gilay-on sulod sa labing menos 20 segundos.',
      'Care Tips': 'Mga Tip sa Pag-atiman',
      'Simple habits for more comfortable screen use.':
          'Yano nga mga batasan para mas komportable ang paggamit sa screen.',
      'Reduce glare': 'Pakunhora ang silaw',
      'Adjust screen brightness and position lights to avoid reflections.':
          'Iangay ang kahayag sa screen ug posisyon sa suga aron malikayan ang repleksyon.',
      'Set up your workspace': 'Ayosa ang imong lugar sa trabaho',
      'Keep a comfortable viewing distance and sit with relaxed posture.':
          'Pagpabilin sa komportableng gilay-on ug paglingkod og tarong.',
      'Settings': 'Mga Setting',
      'Account': 'Account',
      'MataKo user': 'Tiggamit sa MataKo',
      'Remind me while the app is open': 'Pahinumdumi ko samtang bukas ang app',
      'Legal': 'Legal nga Impormasyon',
      'Log out': 'Pag-sign out',
      'Back to Home': 'Balik sa Home',
      'Notifications': 'Mga Pahibalo',
      'Your profile and settings': 'Imong profile ug mga setting',
      'Home': 'Home',
      'Progress': 'Progreso',
      'Tips': 'Mga Tip',
      'Eye break: look 20 feet away for 20 seconds.':
          'Pahuway sa mata: tan-awa ang butang nga 20 ka tiil ang gilay-on sulod sa 20 segundos.',
      'Notification': 'Pahibalo',
      'Filter notifications': 'Pagsala sa mga pahibalo',
      'All': 'Tanan',
      'Today': 'Karon',
      'Yesterday': 'Kagahapon',
      'Screen Break Reminders': 'Mga Pahinumdom sa Pagpahulay',
      '👀 Time to rest your eyes! Look 30 feet away for 30 seconds.':
          '👀 Panahon na sa pagpahulay sa mata! Tan-awa ang 30 ka tiil ang gilay-on sulod sa 30 segundos.',
      'You\'ve been on screen for 1 hour. Take a short break and blink more!':
          'Usa na ka oras ka nagtan-aw sa screen. Pahuway kadiyot ug pagkidlap kanunay!',
      'More eye care tips': 'Dugang mga tip sa pag-atiman sa mata',
      '📝 Haven\'t checked in today? Take your quick eye health check-up now.':
          '📝 Wala pa ka mosusi karon? Buhata na ang dali nga pagsusi sa kahimsog sa mata.',
      '🎉 Congratulations!': '🎉 Mga pahalipay!',
      'You\'ve taken 3 screen breaks today. Great job protecting your eyes!':
          'Mopahuway ka og 3 ka beses karon gikan sa screen. Maayo kaayo ang pag-atiman sa imong mata!',
      'System Alert!': 'Pahibalo sa Sistema!',
      '⚙️ Update your screen break settings for better tracking.':
          '⚙️ I-update ang mga setting sa pahulay aron mas maayo ang pagsubay.',
      'No notifications to show.': 'Walay pahibalo nga ipakita.',
      'I Agree and Continue': 'Miuyon Ko ug Mopadayon',
      'Done': 'Nahuman',
      'Back to Sign Up': 'Balik sa Pag-sign up',
      '1. Acceptance of Terms': '1. Pagsugot sa mga Termino',
      '2. Purpose of the Service': '2. Katuyoan sa Serbisyo',
      '3. User Responsibilities': '3. Responsibilidad sa Gumagamit',
      '4. Health Disclaimer': '4. Pahimangno sa Panglawas',
      '5. Content Usage': '5. Paggamit sa Sulod',
      '1. Information We Collect': '1. Impormasyong Among Kolektahon',
      '2. How We Use Your Data': '2. Giunsa Paggamit ang Imong Datos',
      '3. Data Sharing': '3. Pagpaambit sa Datos',
      '4. Storage and Security': '4. Pagtipig ug Seguridad',
      '5. Your Rights': '5. Imong mga Katungod',
      'Welcome to MataKo, your personal guide to managing Digital Eye Strain (DES). By using MataKo, you agree to these terms. Please read them carefully.':
          'Maayong pag-abot sa MataKo, imong giya sa pagdumala sa Digital Eye Strain (DES). Pinaagi sa paggamit sa MataKo, miuyon ka niining mga termino. Palihog basaha kini pag-ayo.',
      'By accessing and using MataKo, you confirm that you are at least 18 years old and agree to be bound by these Terms of Service.':
          'Pinaagi sa paggamit sa MataKo, gikumpirma nimo nga labing menos 18 anyos ka ug miuyon ka niining mga Termino sa Serbisyo.',
      'MataKo helps adults and working professionals self-assess symptoms and manage Digital Eye Strain through self-evaluation tools, behavior strategies, reminders, and educational content.':
          'Motabang ang MataKo sa mga hamtong ug propesyonal sa pagsusi sa mga sintomas ug pagdumala sa Digital Eye Strain pinaagi sa mga himan sa pagsusi, estratehiya, pahinumdom, ug materyal sa pagkat-on.',
      'You are responsible for keeping your MataKo account confidential and using the app lawfully and respectfully. Assessments and feedback are informational only and do not replace medical advice.':
          'Responsibilidad nimo ang pagpanalipod sa pribasiya sa imong account ug paggamit sa app subay sa balaod ug may pagtahod. Giya lamang ang mga pagsusi ug feedback ug dili kapuli sa tambag sa doktor.',
      'MataKo is not intended to diagnose, treat, or replace professional medical advice. If you have ongoing or severe symptoms, consult a licensed eye care professional.':
          'Dili tuyo sa MataKo ang pag-diagnose o pagtambal ug dili kini kapuli sa tambag sa doktor. Kung magpadayon o grabe ang sintomas, pakigkonsulta sa lisensyadong propesyonal sa mata.',
      'MataKo content, including ergonomic suggestions and educational materials, is provided for personal informational use. Do not reproduce or redistribute it without permission.':
          'Ang sulod sa MataKo, lakip ang mga sugyot sa kahikayan ug materyal sa pagkat-on, para sa personal nga impormasyon. Ayaw kopyaha o ipanghatag nga walay pagtugot.',
      'At MataKo, we value your privacy and are committed to protecting your personal and health-related information.':
          'Gihatagan og bili sa MataKo ang imong pribasiya ug gipanalipdan ang imong personal ug impormasyon sa panglawas.',
      'Personal information: name, email address, phone number, and age. Health data: your self-assessment responses, including Digital Eye Strain symptoms. App usage data may include interactions with features, reminders, and educational tips.':
          'Personal nga impormasyon: ngalan, email, numero sa telepono, ug edad. Datos sa panglawas: imong mga tubag sa pagsusi lakip ang mga sintomas sa Digital Eye Strain. Mahimong maglakip ang datos sa paggamit sa mga feature, pahinumdom, ug tip.',
      'We use information to provide assessment results, personalized care suggestions, reminders, and educational content, and to maintain and improve the app.':
          'Gigamit namo ang impormasyon aron mahatag ang resulta sa pagsusi, mga sugyot, pahinumdom, ug materyal sa pagkat-on, ug aron mapadayon ug mapaayo ang app.',
      'MataKo does not sell your personal information. Data may be used in aggregated form to evaluate and improve the service. We do not share identifying health information without your consent.':
          'Dili ibaligya sa MataKo ang imong personal nga impormasyon. Mahimong gamiton ang hiniusang datos aron masusi ug mapaayo ang serbisyo. Dili ipaambit ang datos sa panglawas nga makaila kanimo kon walay pagtugot.',
      'Your information is stored in the database configured for the MataKo service. Access is limited to the service and its authorized operators. Security protections depend on the environment where the service is deployed.':
          'Gitipigan ang imong impormasyon sa database sa serbisyo sa MataKo. Limitado ang paggamit niini ngadto sa serbisyo ug awtorisadong mga tigdumala. Nagdepende ang seguridad sa palibot diin gipadagan ang serbisyo.',
      'You may request access to or correction of your account information. You may also request account deletion from the MataKo service administrator.':
          'Mahimo kang mohangyo nga makita o matul-id ang impormasyon sa imong account. Mahimo usab kang mohangyo sa tigdumala sa MataKo nga papason ang imong account.',
      'The MataKo server did not respond. Start the Laravel backend and try again.':
          'Walay tubag ang server sa MataKo. Sugdi ang Laravel backend ug sulayi pag-usab.',
      'Cannot connect to the MataKo server. Start the Laravel backend and try again.':
          'Dili makakonekta sa server sa MataKo. Sugdi ang Laravel backend ug sulayi pag-usab.',
    },
  };
}

class _AppLocalizationsDelegate
    extends LocalizationsDelegate<AppLocalizations> {
  const _AppLocalizationsDelegate();

  @override
  bool isSupported(Locale locale) =>
      const {'en', 'fil', 'ceb'}.contains(locale.languageCode);

  @override
  Future<AppLocalizations> load(Locale locale) async =>
      AppLocalizations(locale);

  @override
  bool shouldReload(_AppLocalizationsDelegate old) => false;
}

/// Flutter has no built-in Cebuano Material strings, so use its English
/// built-ins for controls while MataKo supplies Cebuano app text.
class CebuanoMaterialLocalizationsDelegate
    extends LocalizationsDelegate<MaterialLocalizations> {
  const CebuanoMaterialLocalizationsDelegate();

  @override
  bool isSupported(Locale locale) => locale.languageCode == 'ceb';

  @override
  Future<MaterialLocalizations> load(Locale locale) =>
      GlobalMaterialLocalizations.delegate.load(const Locale('en'));

  @override
  bool shouldReload(CebuanoMaterialLocalizationsDelegate old) => false;
}

class CebuanoWidgetsLocalizationsDelegate
    extends LocalizationsDelegate<WidgetsLocalizations> {
  const CebuanoWidgetsLocalizationsDelegate();

  @override
  bool isSupported(Locale locale) => locale.languageCode == 'ceb';

  @override
  Future<WidgetsLocalizations> load(Locale locale) =>
      GlobalWidgetsLocalizations.delegate.load(const Locale('en'));

  @override
  bool shouldReload(CebuanoWidgetsLocalizationsDelegate old) => false;
}

class CebuanoCupertinoLocalizationsDelegate
    extends LocalizationsDelegate<CupertinoLocalizations> {
  const CebuanoCupertinoLocalizationsDelegate();

  @override
  bool isSupported(Locale locale) => locale.languageCode == 'ceb';

  @override
  Future<CupertinoLocalizations> load(Locale locale) =>
      GlobalCupertinoLocalizations.delegate.load(const Locale('en'));

  @override
  bool shouldReload(CebuanoCupertinoLocalizationsDelegate old) => false;
}

extension AppTranslate on BuildContext {
  String tr(String english) => AppLocalizations.of(this).text(english);
}
