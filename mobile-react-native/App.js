import { useEffect, useState } from 'react';
import { createContext, useContext } from 'react';
import Constants from 'expo-constants';
import {
  Alert,
  AppState,
  BackHandler,
  Image,
  KeyboardAvoidingView,
  Modal,
  Platform,
  Pressable,
  RefreshControl,
  SafeAreaView,
  ScrollView,
  StatusBar,
  StyleSheet,
  Switch,
  Text as NativeText,
  TextInput,
  View,
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';

const packagerHost = Constants.expoConfig?.hostUri?.split(':')[0];
const defaultApiHost = Platform.OS === 'android' ? '10.0.2.2' : packagerHost || '127.0.0.1';
const API_URL = process.env.EXPO_PUBLIC_API_URL || `http://${defaultApiHost}:8000/api`;
const ORANGE = '#F58216';
const NAVY = '#062A3C';
const WARM = '#F2EFED';
const WHITE = '#FFFFFF';
const SYMPTOMS = ['Eye pain', 'Dry eyes', 'Blurred vision', 'Headache', 'Eye fatigue'];
const FREQUENCIES = ['None', 'Sometimes', 'Often', 'Always'];
const LANGUAGES = ['English', 'Filipino', 'Cebuano'];

const translations = {
  Filipino: {
    'Get Started': 'Magsimula', 'Select Your Profile Type': 'Piliin ang Uri ng Profile',
    'This will help us tailor your experience.': 'Makakatulong ito para iakma ang iyong karanasan.',
    'Select Student': 'Piliin: Estudyante', 'Select Professional': 'Piliin: Propesyonal',
    'Choose your language:': 'Piliin ang wika:', 'Proceed': 'Magpatuloy', 'Skip for now': 'Laktawan muna',
    'Sign In': 'Mag-sign in', 'Sign Up': 'Mag-sign up', 'Email Address': 'Email Address',
    'Password': 'Password', 'Remember me': 'Tandaan ako', 'Forgot Password?': 'Nakalimutan ang password?',
    'Create Account': 'Gumawa ng account', 'Full Name': 'Buong pangalan', 'Phone Number': 'Numero ng telepono',
    'Age': 'Edad', 'Confirm Password': 'Kumpirmahin ang password', 'Home': 'Home',
    'Progress': 'Progreso', 'Eye Care': 'Pangangalaga sa mata', 'Tips': 'Mga payo', 'Settings': 'Settings',
    'Start Self-Assessment': 'Simulan ang pagsusuri sa mata', 'Assessment History': 'Kasaysayan ng pagsusuri',
    'See my result': 'Tingnan ang resulta', 'Your result': 'Iyong resulta', 'Back to dashboard': 'Bumalik sa dashboard',
    'None': 'Wala', 'Sometimes': 'Minsan', 'Often': 'Madalas', 'Always': 'Palagi',
    'Log out': 'Mag-log out', 'Set Screen Break Reminders': 'Magtakda ng paalala sa pahinga',
  },
  Cebuano: {
    'Get Started': 'Sugdi', 'Select Your Profile Type': 'Pilia ang klase sa profile',
    'This will help us tailor your experience.': 'Makatabang kini sa pagpahaom sa imong kasinatian.',
    'Select Student': 'Pilia: Estudyante', 'Select Professional': 'Pilia: Propesyonal',
    'Choose your language:': 'Pilia ang pinulongan:', 'Proceed': 'Padayon', 'Skip for now': 'Laktawi una',
    'Sign In': 'Sulod', 'Sign Up': 'Paghimo og account', 'Email Address': 'Email address',
    'Password': 'Password', 'Remember me': 'Hinumdumi ako', 'Create Account': 'Paghimo og account',
    'Full Name': 'Bug-os nga ngalan', 'Phone Number': 'Numero sa telepono', 'Age': 'Edad',
    'Confirm Password': 'Kumpirmaha ang password', 'Home': 'Balay', 'Progress': 'Kauswagan',
    'Eye Care': 'Pag-atiman sa mata', 'Tips': 'Mga tambag', 'Settings': 'Mga setting',
    'Start Self-Assessment': 'Sugdi ang pagsusi sa mata', 'Assessment History': 'Kasaysayan sa pagsusi',
    'See my result': 'Tan-awa ang resulta', 'Your result': 'Imong resulta', 'Back to dashboard': 'Balik sa dashboard',
    'None': 'Wala', 'Sometimes': 'Usahay', 'Often': 'Kasagaran', 'Always': 'Kanunay',
    'Log out': 'Gawas', 'Set Screen Break Reminders': 'I-set ang pahinumdom sa pahulay',
  },
};

Object.assign(translations.Filipino, {
  'YOUR VISION, YOUR POWER.': 'ANG IYONG PANINGIN, IYONG LAKAS.', "Select a language you're most comfortable with.": 'Piliin ang wikang pinakakomportable ka.',
  'Already have an account? ': 'May account ka na ba? ', "Don't have an account? ": 'Wala ka pang account? ', 'Welcome to MataKo!': 'Maligayang pagdating sa MataKo!',
  'Your personal companion in protecting your eyes from Digital Eye Strain.': 'Ang iyong katuwang sa pagprotekta sa mga mata laban sa digital eye strain.',
  'Take a quick tour of your eye care dashboard, assessment, progress, and healthy screen habits.': 'Tingnan ang dashboard, pagsusuri, progreso, at mabubuting gawi sa paggamit ng screen.',
  'Take a Tour': 'Tingnan ang gabay', 'Go to Home': 'Pumunta sa Home', 'Quick Tour': 'Maikling gabay',
  '1. Check in with your eyes': '1. Suriin ang kalusugan ng iyong mga mata', 'Answer five questions and get a screening result with practical suggestions.': 'Sagutin ang limang tanong upang makita ang resulta at makatanggap ng mga mungkahi.',
  '2. Track your progress': '2. Subaybayan ang iyong progreso', 'Review your saved assessment history from the Progress tab.': 'Tingnan ang mga na-save na pagsusuri sa tab na Progreso.',
  '3. Build healthy habits': '3. Bumuo ng mabubuting gawi', 'Read eye care tips and turn on 20-minute break reminders.': 'Basahin ang mga payo sa pangangalaga ng mata at i-on ang paalala sa pahinga kada 20 minuto.',
  'Digital eye strain risk · ': 'Panganib ng digital eye strain · ', ' of 15 points': ' sa 15 puntos', 'Suggestions for you': 'Mga mungkahi para sa iyo',
  'This screening result is not a diagnosis. If symptoms persist or concern you, consider speaking with an eye care professional.': 'Hindi ito medikal na diagnosis. Kung magpatuloy o ikabahala mo ang mga sintomas, kumonsulta sa propesyonal sa pangangalaga ng mata.',
  'Loading…': 'Naglo-load…', 'Saving…': 'Sine-save…', 'Over the past week, how often have you experienced each symptom?': 'Sa nakaraang linggo, gaano kadalas mong naranasan ang bawat sintomas?',
  'Eye pain': 'Pananakit ng mata', 'Dry eyes': 'Tuyong mga mata', 'Blurred vision': 'Malabong paningin', 'Headache': 'Sakit ng ulo', 'Eye fatigue': 'Pagkapagod ng mata',
  'Hi, ': 'Kumusta, ', 'Ready to take care of your eyes today?': 'Handa ka na bang alagaan ang iyong mga mata ngayon?',
  'Quick check-up for your eye health': 'Mabilis na pagsusuri para sa kalusugan ng mata', 'View Care Tips': 'Tingnan ang mga payo sa pangangalaga', 'Learn how to protect your vision': 'Alamin kung paano protektahan ang iyong paningin',
  'Schedule healthy breaks from screens': 'Mag-iskedyul ng mabubuting pahinga mula sa screen', 'Eye Care Settings': 'Mga setting ng pangangalaga sa mata', 'Manage your device preferences for your eye care': 'Pamahalaan ang mga setting ng pangangalaga sa mata',
  "Today's Progress": 'Progreso Ngayong Araw', 'Screen breaks': 'Mga pahinga sa screen', 'Time in MataKo': 'Oras sa MataKo', "Today's Eye Tip": 'Payo sa Mata Ngayong Araw',
  'Remember to blink more often while using your screen. The 30-30-30 rule can help reduce eye strain.': 'Madalas na kumurap habang gumagamit ng screen. Makakatulong ang 30-30-30 na tuntunin upang mabawasan ang pagkapagod ng mata.',
  'Recent Activity': 'Kamakailang aktibidad', 'Self-Assessment Completed': 'Natapos ang pagsusuri sa sarili', 'Retake': 'Ulitin', 'No recent activity yet. Start a self-assessment to see your progress here.': 'Wala pang aktibidad. Magsagawa ng pagsusuri upang makita rito ang iyong progreso.',
  'Break Reminder': 'Paalala sa pahinga', 'Assessment History': 'Kasaysayan ng pagsusuri', 'Complete a self-assessment to see your progress here.': 'Kumpletuhin ang pagsusuri sa sarili upang makita rito ang iyong progreso.',
  'Screen break reminders': 'Mga paalala sa pahinga mula sa screen', '20-20-20 break reminder': 'Paalala sa pahingang 20-20-20', 'Get an in-app reminder to look 20 feet away for 20 seconds every 20 minutes.': 'Tumanggap ng paalala kada 20 minuto upang tumingin sa layong 20 talampakan sa loob ng 20 segundo.',
  'Reminder on': 'Naka-on ang paalala', 'Reminder off': 'Naka-off ang paalala', 'Everyday eye care': 'Pang-araw-araw na pangangalaga sa mata', 'Take regular breaks, blink often, and keep your screen at a comfortable distance.': 'Magpahinga nang regular, kumurap nang madalas, at panatilihin ang komportableng layo ng screen.',
  'Eye care tips': 'Mga payo sa pangangalaga ng mata', 'Follow the 20-20-20 rule': 'Sundin ang tuntuning 20-20-20', 'Adjust screen brightness to match your surroundings': 'Itugma ang liwanag ng screen sa liwanag ng paligid', 'Blink often to keep your eyes comfortable': 'Madalas na kumurap upang maging komportable ang mga mata', 'Keep a comfortable distance from your screen': 'Panatilihin ang komportableng layo mula sa screen', 'Take regular breaks and stretch': 'Magpahinga at mag-unat nang regular', 'Small, regular habits can help reduce digital eye strain.': 'Makakatulong ang maliliit at regular na gawi upang mabawasan ang pagkapagod ng mata mula sa screen.',
  'Profile': 'Profile', 'Personal Information': 'Personal na Impormasyon', 'Email': 'Email', 'Phone': 'Telepono', 'User Type': 'Uri ng user', 'Student': 'Estudyante', 'Professional': 'Propesyonal', 'Not provided': 'Hindi ibinigay',
  'Break Reminders': 'Mga paalala sa pahinga', 'Monochrome': 'Monochrome', 'Grayscale mode': 'Grayscale mode', 'Grayscale mode on': 'Naka-on ang grayscale mode', 'Latest Assessment': 'Pinakabagong Pagsusuri', ' Eye Strain': 'Pagkapagod ng Mata',
  'Assessed on ': 'Sinuri noong ', 'Your assessment shows significant symptoms. Consider taking regular breaks and adjusting your screen habits.': 'May mahahalagang sintomas sa resulta. Magpahinga nang regular at ayusin ang mga gawi sa paggamit ng screen.',
  'Your assessment shows moderate symptoms. Consider taking more frequent breaks and adjusting screen brightness.': 'May katamtamang sintomas sa resulta. Magpahinga nang mas madalas at ayusin ang liwanag ng screen.', 'Your assessment shows mild symptoms. Keep practicing healthy screen habits and taking regular breaks.': 'May bahagyang sintomas sa resulta. Ipagpatuloy ang mabubuting gawi sa screen at regular na pagpapahinga.',
  'Complete a self-assessment to see your latest result here.': 'Kumpletuhin ang pagsusuri sa sarili upang makita rito ang pinakabagong resulta.', 'View All Results': 'Tingnan ang Lahat ng Resulta',
  'Language': 'Wika', 'Edit Profile': 'I-edit ang Profile', 'Full Name': 'Buong pangalan', 'Save Changes': 'I-save ang mga pagbabago', 'Back': 'Bumalik', 'Back to Sign Up': 'Bumalik sa Pagpaparehistro',
  'Notifications': 'Mga notification', 'Today': 'Ngayon', 'Yesterday': 'Kahapon', 'Earlier': 'Mas maaga', 'Assessment completed': 'Natapos ang pagsusuri', 'View result': 'Tingnan ang resulta',
  'Reminders are enabled. Keep MataKo open to receive your scheduled break prompts.': 'Naka-on ang mga paalala. Panatilihing bukas ang MataKo upang matanggap ang mga nakaiskedyul na paalala.', 'Break reminders are currently turned off. You can enable them in Eye Care.': 'Naka-off ang mga paalala. Maaari mo itong i-on sa Pangangalaga sa Mata.',
  'Manage reminders': 'Pamahalaan ang mga paalala', 'Set up reminders': 'I-set up ang mga paalala', 'No self-assessments completed today.': 'Wala pang natatapos na pagsusuri ngayong araw.', 'There are no earlier assessment updates.': 'Walang mas naunang update ng pagsusuri.', 'I Agree and Continue': 'Sumasang-ayon ako at Magpatuloy', 'Done': 'Tapos na',
  'Terms of Service': 'Mga Tuntunin ng Serbisyo', 'Privacy Policy': 'Patakaran sa Privacy',
  'Welcome to MataKo, your personal guide to managing Digital Eye Strain (DES). By using MataKo, you agree to abide by the following terms. Please read carefully.': 'Maligayang pagdating sa MataKo, ang iyong gabay sa pamamahala ng digital eye strain (DES). Sa paggamit ng MataKo, sumasang-ayon kang sundin ang mga tuntuning ito. Basahin nang mabuti.',
  '1. Acceptance of Terms': '1. Pagtanggap sa mga Tuntunin', 'By accessing and using MataKo, you confirm that you are at least 18 years old and agree to be bound by these Terms of Service.': 'Sa paggamit ng MataKo, pinatutunayan mong ikaw ay hindi bababa sa 18 taong gulang at sumasang-ayon ka sa mga Tuntunin ng Serbisyong ito.',
  '2. Purpose of the Service': '2. Layunin ng Serbisyo', 'MataKo is designed to help young adults and working professionals self-assess and manage symptoms of Digital Eye Strain through self-evaluation tools, behavior-based strategies, reminders, and educational content.': 'Dinisenyo ang MataKo upang tulungan ang mga young adult at propesyonal na suriin at pamahalaan ang mga sintomas ng digital eye strain gamit ang mga tool sa pagsusuri, estratehiya sa gawi, paalala, at kaalamang pang-edukasyon.',
  '3. User Responsibilities': '3. Mga Responsibilidad ng User', 'You are responsible for maintaining the confidentiality of your MataKo account and agree to use the app in a lawful and respectful manner. All assessments and feedback are for informational purposes only and do not substitute for medical advice.': 'Responsibilidad mong panatilihing kumpidensyal ang iyong account at gamitin ang app nang naaayon sa batas at may paggalang. Para lamang sa impormasyon ang mga pagsusuri at puna at hindi kapalit ng payong medikal.',
  '4. Health Disclaimer': '4. Paalala sa Kalusugan', 'MataKo is not intended to diagnose, treat, or replace professional medical advice. Users with ongoing or severe eye symptoms are strongly encouraged to consult a licensed eye care professional.': 'Hindi nilayon ang MataKo na mag-diagnose, gumamot, o pumalit sa propesyonal na payong medikal. Hinihikayat ang mga may nagpapatuloy o malulubhang sintomas sa mata na kumonsulta sa lisensyadong propesyonal.',
  '5. Content Usage': '5. Paggamit ng Nilalaman', 'All content such as ergonomic advice, educational materials, and care suggestions provided in MataKo is protected and may not be reproduced or redistributed without permission.': 'Protektado ang lahat ng nilalaman gaya ng payo sa ergonomiya, materyales pang-edukasyon, at mungkahi sa pangangalaga; huwag itong kopyahin o ipamahagi nang walang pahintulot.',
  'At MataKo, we value your privacy and are committed to protecting your personal and health-related information.': 'Pinahahalagahan ng MataKo ang iyong privacy at nakatuon ito sa pagprotekta sa iyong personal at impormasyong pangkalusugan.',
  '1. Information We Collect': '1. Impormasyong Kinokolekta Namin', 'Personal Information: Name, email (if account is created), and age group.\nHealth Data: Your self-assessment responses (e.g., DES symptoms, screen time habits).\nApp Usage Data: Frequency of feature usage, interactions with reminders and educational tips.': 'Personal na Impormasyon: Pangalan, email (kung gumawa ng account), at pangkat ng edad.\nDatos Pangkalusugan: Mga sagot sa pagsusuri sa sarili, gaya ng sintomas ng DES at gawi sa paggamit ng screen.\nDatos sa Paggamit ng App: Dalas ng paggamit ng mga feature at pakikipag-ugnayan sa mga paalala at payong pang-edukasyon.',
  '2. How We Use Your Data': '2. Paano Namin Ginagamit ang Iyong Datos', 'To generate personalized care suggestions and ergonomic guidance.\nTo provide culturally relevant health education.\nTo improve app performance and user experience.\nFor anonymous research purposes to enhance DES prevention tools.': 'Upang gumawa ng mga mungkahing angkop sa iyo at gabay sa ergonomiya.\nUpang magbigay ng kaalamang pangkalusugan na angkop sa kultura.\nUpang pagandahin ang performance ng app at karanasan ng user.\nPara sa hindi nagpapakilalang pananaliksik upang mapahusay ang mga tool sa pag-iwas sa DES.',
  '3. Data Sharing': '3. Pagbabahagi ng Datos', 'We do not sell your data. Data may be shared in aggregated, anonymized form for academic or product development purposes. No individual information is ever shared without consent.': 'Hindi namin ipinagbibili ang iyong datos. Maaaring ibahagi ang pinagsama-sama at hindi nagpapakilalang datos para sa akademiko o pagbuo ng produkto. Hindi ibabahagi ang personal na impormasyon nang walang pahintulot.',
  '4. Storage and Security': '4. Pag-iimbak at Seguridad', 'Your data is securely stored and encrypted. We implement industry-standard security measures to protect against unauthorized access.': 'Ligtas na iniimbak at ine-encrypt ang iyong datos. Gumagamit kami ng mga pamantayang hakbang sa seguridad upang maprotektahan ito laban sa hindi awtorisadong pag-access.',
  '5. User Rights': '5. Mga Karapatan ng User', 'You have the right to:\n• Access your data.\n• Request correction or deletion.\n• Withdraw consent at any time by deleting your account or contacting us.': 'May karapatan kang:\n• I-access ang iyong datos.\n• Humiling ng pagwawasto o pagbura nito.\n• Bawiin ang pahintulot anumang oras sa pagbura ng account o pakikipag-ugnayan sa amin.',
});

Object.assign(translations.Cebuano, {
  'Hi, ': 'Kumusta, ',
  'YOUR VISION, YOUR POWER.': 'IMONG PANAN-AW, IMONG KUSOG.', "Select a language you're most comfortable with.": 'Pilia ang pinulongan nga labing komportable ka gamiton.',
  'Already have an account? ': 'Aduna na kay account? ', "Don't have an account? ": 'Wala pa kay account? ', 'Welcome to MataKo!': 'Maayong pag-abot sa MataKo!',
  'Your personal companion in protecting your eyes from Digital Eye Strain.': 'Imong kauban sa pagpanalipod sa imong mga mata batok sa digital eye strain.',
  'Take a quick tour of your eye care dashboard, assessment, progress, and healthy screen habits.': 'Tan-awa ang dashboard sa pag-atiman sa mata, pagtimbang-timbang, progreso, ug maayong batasan sa paggamit sa screen.',
  'Take a Tour': 'Tan-awa ang giya', 'Go to Home': 'Adto sa Home', 'Quick Tour': 'Mubo nga giya',
  '1. Check in with your eyes': '1. Susiha ang kahimsog sa imong mga mata', 'Answer five questions and get a screening result with practical suggestions.': 'Tubaga ang lima ka pangutana aron makita ang resulta ug makadawat og praktikal nga mga sugyot.',
  '2. Track your progress': '2. Bantayi ang imong progreso', 'Review your saved assessment history from the Progress tab.': 'Tan-awa ang natipig nga kasaysayan sa pagsusi sa tab nga Kauswagan.',
  '3. Build healthy habits': '3. Himoa ang maayong mga batasan', 'Read eye care tips and turn on 20-minute break reminders.': 'Basaha ang mga tambag sa pag-atiman sa mata ug i-on ang pahinumdom sa pagpahulay matag 20 minutos.',
  'Digital eye strain risk · ': 'Peligro sa digital eye strain · ', ' of 15 points': ' sa 15 ka puntos', 'Suggestions for you': 'Mga sugyot para kanimo',
  'This screening result is not a diagnosis. If symptoms persist or concern you, consider speaking with an eye care professional.': 'Dili kini medikal nga diagnosis. Kon magpadayon o makapabalaka ang mga sintomas, pakigkonsulta sa propesyonal sa pag-atiman sa mata.',
  'Loading…': 'Nagkarga…', 'Saving…': 'Gitipigan…', 'Over the past week, how often have you experienced each symptom?': 'Sa miaging semana, unsa ka kanunay nimong nasinati ang matag sintomas?',
  'Eye pain': 'Sakit sa mata', 'Dry eyes': 'Uga nga mga mata', 'Blurred vision': 'Hanap nga panan-aw', 'Headache': 'Sakit sa ulo', 'Eye fatigue': 'Kakapoy sa mata',
  'Ready to take care of your eyes today?': 'Andam ka ba nga moatiman sa imong mga mata karon?', 'Quick check-up for your eye health': 'Mubo nga pagsusi sa kahimsog sa mata', 'View Care Tips': 'Tan-awa ang mga tambag sa pag-atiman', 'Learn how to protect your vision': 'Hibal-i unsaon pagpanalipod sa imong panan-aw',
  'Schedule healthy breaks from screens': 'I-iskedyul ang maayong pagpahulay gikan sa screen', 'Eye Care Settings': 'Mga setting sa pag-atiman sa mata', 'Manage your device preferences for your eye care': 'I-adjust ang mga setting sa device para sa pag-atiman sa imong mata',
  "Today's Progress": 'Kauswagan Karon', 'Screen breaks': 'Mga pahulay sa screen', 'Time in MataKo': 'Oras sa MataKo', "Today's Eye Tip": 'Tambag sa Mata Karon',
  'Remember to blink more often while using your screen. The 30-30-30 rule can help reduce eye strain.': 'Hinumdomi nga mokurap kanunay samtang naggamit sa screen. Makatabang ang lagda nga 30-30-30 sa pagpakunhod sa kakapoy sa mata.',
  'Recent Activity': 'Bag-ong kalihokan', 'Self-Assessment Completed': 'Nahuman ang pagsusi sa kaugalingon', 'Retake': 'Usba', 'No recent activity yet. Start a self-assessment to see your progress here.': 'Wala pay bag-ong kalihokan. Pagsusi sa kaugalingon aron makita dinhi ang imong progreso.',
  'Break Reminder': 'Pahinumdom sa pahulay', 'Assessment History': 'Kasaysayan sa pagsusi', 'Complete a self-assessment to see your progress here.': 'Kompletoha ang pagsusi sa kaugalingon aron makita dinhi ang imong progreso.',
  'Screen break reminders': 'Mga pahinumdom sa pahulay gikan sa screen', '20-20-20 break reminder': 'Pahinumdom sa pahulay nga 20-20-20', 'Get an in-app reminder to look 20 feet away for 20 seconds every 20 minutes.': 'Makadawat og pahinumdom matag 20 minutos nga motan-aw sa gilay-on nga 20 ka tiil sulod sa 20 segundos.',
  'Reminder on': 'Naka-on ang pahinumdom', 'Reminder off': 'Naka-off ang pahinumdom', 'Everyday eye care': 'Adlaw-adlaw nga pag-atiman sa mata', 'Take regular breaks, blink often, and keep your screen at a comfortable distance.': 'Pahuway kanunay, kurap kanunay, ug ipahilayo ang screen sa komportableng gilay-on.',
  'Eye care tips': 'Mga tambag sa pag-atiman sa mata', 'Follow the 20-20-20 rule': 'Sunda ang lagda nga 20-20-20', 'Adjust screen brightness to match your surroundings': 'Ipares ang kahayag sa screen sa kahayag sa palibot', 'Blink often to keep your eyes comfortable': 'Kurap kanunay aron komportable ang imong mga mata', 'Keep a comfortable distance from your screen': 'Pagpabiling komportable ang gilay-on sa screen', 'Take regular breaks and stretch': 'Pahuway ug pag-unat kanunay', 'Small, regular habits can help reduce digital eye strain.': 'Makatabang ang gagmay ug kanunay nga maayong batasan sa pagpakunhod sa kakapoy sa mata tungod sa screen.',
  'Profile': 'Profile', 'Personal Information': 'Personal nga Impormasyon', 'Email': 'Email', 'Phone': 'Telepono', 'User Type': 'Klase sa user', 'Student': 'Estudyante', 'Professional': 'Propesyonal', 'Not provided': 'Wala gihatag',
  'Break Reminders': 'Mga pahinumdom sa pahulay', 'Monochrome': 'Monochrome', 'Grayscale mode': 'Grayscale mode', 'Grayscale mode on': 'Naka-on ang grayscale mode', 'Latest Assessment': 'Pinakabag-ong Pagsusi', ' Eye Strain': 'Kakapoy sa Mata',
  'Assessed on ': 'Gisusi niadtong ', 'Your assessment shows significant symptoms. Consider taking regular breaks and adjusting your screen habits.': 'Nagpakita ang resulta og dakong mga sintomas. Pahuway kanunay ug usba ang imong batasan sa paggamit sa screen.',
  'Your assessment shows moderate symptoms. Consider taking more frequent breaks and adjusting screen brightness.': 'Nagpakita ang resulta og kasarangang mga sintomas. Pahuway kanunay ug usba ang kahayag sa screen.', 'Your assessment shows mild symptoms. Keep practicing healthy screen habits and taking regular breaks.': 'Nagpakita ang resulta og gaan nga mga sintomas. Padayon sa maayong batasan sa screen ug kanunay nga pagpahulay.',
  'Complete a self-assessment to see your latest result here.': 'Kompletoha ang pagsusi sa kaugalingon aron makita dinhi ang pinakabag-ong resulta.', 'View All Results': 'Tan-awa ang Tanang Resulta',
  'Language': 'Pinulongan', 'Edit Profile': 'Usba ang Profile', 'Full Name': 'Bug-os nga ngalan', 'Save Changes': 'Tipigi ang mga kausaban', 'Back': 'Balik', 'Back to Sign Up': 'Balik sa Pagparehistro',
  'Notifications': 'Mga pahibalo', 'Today': 'Karon', 'Yesterday': 'Kagahapon', 'Earlier': 'Una pa', 'Assessment completed': 'Nahuman ang pagsusi', 'View result': 'Tan-awa ang resulta',
  'Reminders are enabled. Keep MataKo open to receive your scheduled break prompts.': 'Naka-on ang mga pahinumdom. Bilina nga bukas ang MataKo aron madawat ang naka-iskedyul nga pahinumdom.', 'Break reminders are currently turned off. You can enable them in Eye Care.': 'Naka-off ang mga pahinumdom. Mahimo nimo kining i-on sa Pag-atiman sa Mata.',
  'Manage reminders': 'Pamahalaa ang mga pahinumdom', 'Set up reminders': 'I-set up ang mga pahinumdom', 'No self-assessments completed today.': 'Walay nahuman nga pagsusi karong adlawa.', 'There are no earlier assessment updates.': 'Walay naunang update sa pagsusi.', 'I Agree and Continue': 'Mouyon ko ug Padayon', 'Done': 'Nahuman',
  'Terms of Service': 'Mga Termino sa Serbisyo', 'Privacy Policy': 'Patakaran sa Privacy',
  'Welcome to MataKo, your personal guide to managing Digital Eye Strain (DES). By using MataKo, you agree to abide by the following terms. Please read carefully.': 'Maayong pag-abot sa MataKo, imong giya sa pagdumala sa digital eye strain (DES). Pinaagi sa paggamit sa MataKo, mouyon ka sa mosunod nga mga termino. Palihog basaha pag-ayo.',
  '1. Acceptance of Terms': '1. Pagdawat sa mga Termino', 'By accessing and using MataKo, you confirm that you are at least 18 years old and agree to be bound by these Terms of Service.': 'Pinaagi sa paggamit sa MataKo, imong gipamatud-an nga nag-edad ka og labing menos 18 ka tuig ug mouyon ka nga sundon kining mga Termino sa Serbisyo.',
  '2. Purpose of the Service': '2. Katuyoan sa Serbisyo', 'MataKo is designed to help young adults and working professionals self-assess and manage symptoms of Digital Eye Strain through self-evaluation tools, behavior-based strategies, reminders, and educational content.': 'Gidesinyo ang MataKo aron tabangan ang mga batan-on ug propesyonal sa pagsusi ug pagdumala sa mga sintomas sa digital eye strain pinaagi sa mga himan sa pagsusi, estratehiya sa batasan, pahinumdom, ug impormasyon.',
  '3. User Responsibilities': '3. Mga Responsibilidad sa User', 'You are responsible for maintaining the confidentiality of your MataKo account and agree to use the app in a lawful and respectful manner. All assessments and feedback are for informational purposes only and do not substitute for medical advice.': 'Responsibilidad nimo ang pagpanalipod sa pribasiya sa imong account ug mouyon ka sa paggamit sa app subay sa balaod ug may pagtahod. Impormasyon lamang ang mga pagsusi ug tubag; dili kini kapuli sa tambag medikal.',
  '4. Health Disclaimer': '4. Pahimangno sa Panglawas', 'MataKo is not intended to diagnose, treat, or replace professional medical advice. Users with ongoing or severe eye symptoms are strongly encouraged to consult a licensed eye care professional.': 'Dili gituyo ang MataKo sa pagdayagnos, pagtambal, o pagpuli sa tambag sa propesyonal sa panglawas. Giawhag ang adunay nagpadayon o grabe nga sintomas sa mata nga mokonsulta sa lisensyadong propesyonal.',
  '5. Content Usage': '5. Paggamit sa Sulod', 'All content such as ergonomic advice, educational materials, and care suggestions provided in MataKo is protected and may not be reproduced or redistributed without permission.': 'Protektado ang tanang sulod sama sa tambag sa ergonomiya, materyales sa edukasyon, ug sugyot sa pag-atiman sa MataKo; dili kini kopyahon o ipanghatag pag-usab nga walay pagtugot.',
  'At MataKo, we value your privacy and are committed to protecting your personal and health-related information.': 'Gihatagan og bili sa MataKo ang imong pribasiya ug gipanalipdan ang imong personal ug impormasyon bahin sa panglawas.',
  '1. Information We Collect': '1. Impormasyong Among Kolektahon', 'Personal Information: Name, email (if account is created), and age group.\nHealth Data: Your self-assessment responses (e.g., DES symptoms, screen time habits).\nApp Usage Data: Frequency of feature usage, interactions with reminders and educational tips.': 'Personal nga Impormasyon: Ngalan, email (kon naghimo og account), ug grupo sa edad.\nDatos sa Panglawas: Mga tubag sa pagsusi sa kaugalingon, sama sa sintomas sa DES ug batasan sa screen.\nDatos sa Paggamit sa App: Kasubsob sa paggamit sa mga feature, pahinumdom, ug tambag.',
  '2. How We Use Your Data': '2. Giunsa Paggamit ang Imong Datos', 'To generate personalized care suggestions and ergonomic guidance.\nTo provide culturally relevant health education.\nTo improve app performance and user experience.\nFor anonymous research purposes to enhance DES prevention tools.': 'Aron makahimo og mga sugyot ug giya nga angay kanimo.\nAron mohatag og edukasyon sa panglawas nga angay sa kultura.\nAron mapaayo ang performance sa app ug kasinatian sa user.\nAlang sa dili mailhing panukiduki aron mapaayo ang mga himan sa pagpugong sa DES.',
  '3. Data Sharing': '3. Pagpaambit sa Datos', 'We do not sell your data. Data may be shared in aggregated, anonymized form for academic or product development purposes. No individual information is ever shared without consent.': 'Dili namo ibaligya ang imong datos. Mahimong ipaambit ang kinatibuk-an ug dili mailhing datos alang sa akademiko o pagpalambo sa produkto. Dili ipaambit ang personal nga impormasyon kon walay pagtugot.',
  '4. Storage and Security': '4. Pagtipig ug Seguridad', 'Your data is securely stored and encrypted. We implement industry-standard security measures to protect against unauthorized access.': 'Luwas nga gitipigan ug gi-encrypt ang imong datos. Gigamit namo ang mga sukdanan sa seguridad sa industriya aron mapanalipdan kini batok sa walay pagtugot nga pag-access.',
  '5. User Rights': '5. Mga Katungod sa User', 'You have the right to:\n• Access your data.\n• Request correction or deletion.\n• Withdraw consent at any time by deleting your account or contacting us.': 'Aduna kay katungod sa:\n• Pag-access sa imong datos.\n• Paghangyo nga usbon o tangtangon kini.\n• Pagbawi sa pagtugot bisan kanus-a pinaagi sa pagtangtang sa account o pagkontak kanamo.',
});

Object.assign(translations.Filipino, {
  'Name': 'Pangalan', 'Hide': 'Itago', 'Show': 'Ipakita', 'Sign in to your account to continue': 'Mag-sign in sa iyong account para magpatuloy',
  'Join us and start your journey today': 'Sumali at simulan ang iyong paglalakbay ngayon', 'I agree to the ': 'Sumasang-ayon ako sa ', ' and ': ' at ',
  'or continue with': 'o magpatuloy gamit ang', 'Continue with Google': 'Magpatuloy gamit ang Google', 'Continue with Apple': 'Magpatuloy gamit ang Apple',
  'Creating account…': 'Gumagawa ng account…', 'Signing in…': 'Nag-sign in…', 'Enter a valid email and password.': 'Maglagay ng wastong email at password.',
  'Complete each field. You must be at least 18 to sign up.': 'Kumpletuhin ang bawat patlang. Dapat ay 18 taong gulang pataas upang magparehistro.',
  'Use at least 8 characters and make sure the passwords match.': 'Gumamit ng hindi bababa sa 8 character at tiyaking magkapareho ang mga password.',
  'Please agree to the Terms and Privacy Policy.': 'Sumang-ayon sa Mga Tuntunin at Patakaran sa Privacy.', 'Forgot Password?': 'Nakalimutan ang password?',
  'Password reset is not configured yet.': 'Hindi pa naka-set up ang pag-reset ng password.', 'Unavailable': 'Hindi magagamit', 'Google sign-in is not configured yet.': 'Hindi pa naka-set up ang pag-sign in gamit ang Google.', 'Apple sign-in is not configured yet.': 'Hindi pa naka-set up ang pag-sign in gamit ang Apple.',
  'Profile options': 'Mga opsyon sa profile', 'Choose an account action.': 'Pumili ng gagawin sa account.', 'Cancel': 'Kanselahin', 'Back to Home': 'Bumalik sa Home',
  'More eye care settings': 'Iba pang setting sa pangangalaga ng mata', 'Every 20 minutes': 'Bawat 20 minuto', 'Screen Time': 'Oras sa Screen', 'Breaks': 'Mga Pahinga', 'Streak': 'Sunod-sunod na araw',
    ' years': ' taon', 'User': 'Kaibigan', 'Mild Eye Strain': 'Bahagyang Pagkapagod ng Mata', 'Moderate Eye Strain': 'Katamtamang Pagkapagod ng Mata', 'High Eye Strain': 'Matinding Pagkapagod ng Mata',
  'Please try again.': 'Pakisubukang muli.', 'Eye break': 'Pahinga ng mata', 'Look 20 feet away for 20 seconds.': 'Tumingin sa layong 20 talampakan sa loob ng 20 segundo.',
});
Object.assign(translations.Cebuano, {
  'Name': 'Ngalan', 'Hide': 'Tagoa', 'Show': 'Ipakita', 'Sign in to your account to continue': 'Sulod sa imong account aron mopadayon',
  'Join us and start your journey today': 'Apil kanamo ug sugdi karon ang imong panaw', 'I agree to the ': 'Mouyon ko sa ', ' and ': ' ug ',
  'or continue with': 'o padayon gamit ang', 'Continue with Google': 'Padayon gamit ang Google', 'Continue with Apple': 'Padayon gamit ang Apple',
  'Creating account…': 'Naghimo og account…', 'Signing in…': 'Nagasulod…', 'Enter a valid email and password.': 'Pagsulod og balidong email ug password.',
  'Complete each field. You must be at least 18 to sign up.': 'Kompletoha ang matag kahon. Kinahanglan 18 anyos pataas aron magparehistro.',
  'Use at least 8 characters and make sure the passwords match.': 'Gamit og labing menos 8 ka karakter ug siguroha nga magkapareho ang mga password.',
  'Please agree to the Terms and Privacy Policy.': 'Palihog mouyon sa mga Termino ug Patakaran sa Privacy.', 'Forgot Password?': 'Nakalimot sa password?',
  'Password reset is not configured yet.': 'Wala pa ma-set up ang pag-reset sa password.', 'Unavailable': 'Dili magamit', 'Google sign-in is not configured yet.': 'Wala pa ma-set up ang pagsulod gamit ang Google.', 'Apple sign-in is not configured yet.': 'Wala pa ma-set up ang pagsulod gamit ang Apple.',
  'Profile options': 'Mga opsyon sa profile', 'Choose an account action.': 'Pilia ang buhaton sa account.', 'Cancel': 'Kanselahon', 'Back to Home': 'Balik sa Home',
  'More eye care settings': 'Dugang nga setting sa pag-atiman sa mata', 'Every 20 minutes': 'Matag 20 minutos', 'Screen Time': 'Oras sa Screen', 'Breaks': 'Mga Pahulay', 'Streak': 'Sunod-sunod nga adlaw',
  ' years': ' ka tuig', 'User': 'Gumagamit', 'Mild Eye Strain': 'Gaan nga Kakapoy sa Mata', 'Moderate Eye Strain': 'Kasarangang Kakapoy sa Mata', 'High Eye Strain': 'Grabe nga Kakapoy sa Mata',
  'Please try again.': 'Palihog sulayi pag-usab.', 'Eye break': 'Pahulay sa mata', 'Look 20 feet away for 20 seconds.': 'Tan-aw sa gilay-on nga 20 ka tiil sulod sa 20 segundos.',
});
Object.assign(translations.Filipino, {
  today: 'ngayong araw', risk: 'panganib', 'of 15': 'sa 15', 'LOW': 'Mababa', 'MEDIUM': 'Katamtaman', 'HIGH': 'Mataas',
  years: 'taon', day: 'araw', days: 'araw', minute: 'minuto', minutes: 'minuto', hour: 'oras', hours: 'oras', ago: 'ang nakalipas',
  'Your result is ready:': 'Handa na ang iyong resulta:', 'No recent activity yet. Start a self-assessment to see your results here.': 'Wala pang aktibidad. Magsagawa ng pagsusuri upang makita rito ang mga resulta.',
  'Show recent notifications': 'Ipakita ang mga kamakailang notification', 'Show earlier notifications': 'Ipakita ang mas naunang notification',
  'Join us and start your journey today': 'Sumali at simulan ang iyong paglalakbay ngayon', 'or continue with': 'o magpatuloy gamit ang',
});
Object.assign(translations.Cebuano, {
  today: 'karon', risk: 'peligro', 'of 15': 'sa 15', 'LOW': 'Ubos', 'MEDIUM': 'Kasagaran', 'HIGH': 'Taas',
  years: 'ka tuig', day: 'adlaw', days: 'adlaw', minute: 'minuto', minutes: 'minuto', hour: 'oras', hours: 'oras', ago: 'ang milabay',
  'Your result is ready:': 'Andam na ang imong resulta:', 'No recent activity yet. Start a self-assessment to see your results here.': 'Wala pay kalihokan. Pagsusi sa kaugalingon aron makita dinhi ang mga resulta.',
  'Show recent notifications': 'Ipakita ang bag-ong mga pahibalo', 'Show earlier notifications': 'Ipakita ang naunang mga pahibalo',
});
Object.assign(translations.Filipino, {
  'break reminder has appeared while MataKo was open today.': 'paalala sa pahinga ang lumabas habang bukas ang MataKo ngayong araw.',
  'break reminders have appeared while MataKo was open today.': 'mga paalala sa pahinga ang lumabas habang bukas ang MataKo ngayong araw.',
  'Maintain good screen habits': 'Panatilihin ang mabubuting gawi sa paggamit ng screen', 'Take occasional breaks': 'Magpahinga paminsan-minsan', 'Follow the 20-20-20 rule': 'Sundin ang tuntuning 20-20-20',
  'Adjust screen brightness': 'Ayusin ang liwanag ng screen', 'Blink more often': 'Kumurap nang mas madalas', 'Keep a strict break schedule': 'Sundin ang iskedyul ng pahinga', 'Reduce screen time': 'Bawasan ang oras sa screen', 'Improve your ergonomics': 'Pagbutihin ang ergonomiya ng paggamit', 'Consider consulting an eye specialist': 'Isaalang-alang ang pagkonsulta sa espesyalista sa mata',
});
Object.assign(translations.Cebuano, {
  'break reminder has appeared while MataKo was open today.': 'ka pahinumdom sa pahulay ang migawas samtang bukas ang MataKo karon.',
  'break reminders have appeared while MataKo was open today.': 'ka mga pahinumdom sa pahulay ang migawas samtang bukas ang MataKo karon.',
  'Maintain good screen habits': 'Hupti ang maayong batasan sa paggamit sa screen', 'Take occasional breaks': 'Pahuway usahay', 'Follow the 20-20-20 rule': 'Sunda ang lagda nga 20-20-20',
  'Adjust screen brightness': 'I-adjust ang kahayag sa screen', 'Blink more often': 'Kurap kanunay', 'Keep a strict break schedule': 'Sunda ang iskedyul sa pagpahulay', 'Reduce screen time': 'Pakunhori ang oras sa screen', 'Improve your ergonomics': 'Pauswaga ang hustong pamaagi sa paggamit', 'Consider consulting an eye specialist': 'Hunahunaa ang pagkonsulta sa espesyalista sa mata',
});
Object.assign(translations.Filipino, {
  'Ready to check your eye health?': 'Handa ka na bang suriin ang kalusugan ng iyong mga mata?',
  'This quick self-assessment will only take a few minutes.': 'Aabutin lamang ng ilang minuto ang mabilis na pagsusuring ito.',
  'Ready to learn how to take care of your eyes?': 'Handa ka na bang matutunan kung paano alagaan ang iyong mga mata?',
  "We've prepared some simple tips to help reduce eye strain.": 'Naghanda kami ng ilang simpleng payo upang mabawasan ang pagkapagod ng mata.',
  'Want to take healthy screen breaks?': 'Gusto mo bang magkaroon ng mabuting pahinga mula sa screen?',
  'Set gentle reminders to protect your eyes during long screen use.': 'Magtakda ng banayad na mga paalala upang maprotektahan ang iyong mga mata sa matagal na paggamit ng screen.',
  'Customize your eye care experience.': 'Iangkop ang iyong karanasan sa pangangalaga ng mata.',
  'You can enable features like dark mode, dimmers, and pop-up breaks.': 'Maaari mong i-on ang mga feature gaya ng dark mode, dimmer, at mga paalala sa pahinga.',
  'Open': 'Buksan',
  'Cancel': 'Kanselahin', 'Next': 'Susunod',
});
Object.assign(translations.Cebuano, {
  'Ready to check your eye health?': 'Andam na ba ka nga susihon ang kahimsog sa imong mga mata?',
  'This quick self-assessment will only take a few minutes.': 'Pipila lang ka minuto kining mubo nga pagsusi sa kaugalingon.',
  'Ready to learn how to take care of your eyes?': 'Andam na ba ka nga makat-on unsaon pag-atiman sa imong mga mata?',
  "We've prepared some simple tips to help reduce eye strain.": 'Nangandam kami og pipila ka yano nga tambag aron mamenosan ang kakapoy sa mata.',
  'Want to take healthy screen breaks?': 'Gusto ba ka nga magpahulay kanunay gikan sa screen?',
  'Set gentle reminders to protect your eyes during long screen use.': 'Pagbutang og pahinumdom aron mapanalipdan ang imong mga mata kon dugay kang mogamit og screen.',
  'Customize your eye care experience.': 'Ipasibo ang imong kasinatian sa pag-atiman sa mata.',
  'You can enable features like dark mode, dimmers, and pop-up breaks.': 'Mahimo nimong i-on ang dark mode, dimmer, ug mga pahinumdom sa pagpahulay.',
  'Open': 'Ablihi',
  'Cancel': 'Ikansela', 'Next': 'Sunod',
});

const LanguageContext = createContext('English');
function Text({ children, ...props }) {
  const language = useContext(LanguageContext);
  const translateChild = (child) => {
    if (typeof child === 'string') return translations[language]?.[child] || child;
    if (Array.isArray(child)) return child.map(translateChild);
    return child;
  };
  return <NativeText {...props}>{translateChild(children)}</NativeText>;
}

async function request(path, { token, method = 'GET', body } = {}) {
  let response;
  try {
    response = await fetch(`${API_URL}${path}`, {
      method,
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
      ...(body ? { body: JSON.stringify(body) } : {}),
    });
  } catch {
    throw new Error(`Cannot connect to Laravel at ${API_URL}. Keep the phone and computer on the same Wi-Fi, start Laravel with --host 0.0.0.0, and check that Windows allows port 8000.`);
  }
  const data = await response.json().catch(() => ({}));
  if (!response.ok) {
    const firstErrors = data.errors ? Object.values(data.errors).flat() : [];
    throw new Error(firstErrors[0] || data.message || 'Request failed. Please try again.');
  }
  return data;
}

export default function App() {
  const [screen, setScreen] = useState('loading');
  const [homeTab, setHomeTab] = useState('home');
  const [language, setLanguage] = useState('English');
  const [role, setRole] = useState('student');
  const [token, setToken] = useState(null);
  const [user, setUser] = useState(null);
  const [assessments, setAssessments] = useState([]);
  const [result, setResult] = useState(null);
  const [legalTab, setLegalTab] = useState('Terms of Service');
  const [legalOrigin, setLegalOrigin] = useState('register');
  const [legalAccepted, setLegalAccepted] = useState(false);
  const [registerValues, setRegisterValues] = useState({ name: '', email: '', phone: '', age: '', password: '', password_confirmation: '' });
  const [remindersOn, setRemindersOn] = useState(false);
  const [monochrome, setMonochrome] = useState(false);
  const [remindersToday, setRemindersToday] = useState(0);
  const [appMinutes, setAppMinutes] = useState(0);
  const [showEarlierNotifications, setShowEarlierNotifications] = useState(false);
  const [loading, setLoading] = useState(false);
  const t = (value) => translations[language]?.[value] || value;

  useEffect(() => {
    if (Platform.OS !== 'android') return undefined;

    const subscription = BackHandler.addEventListener('hardwareBackPress', () => {
      const previousScreen = {
        profile: 'welcome',
        language: 'profile',
        login: 'welcome',
        register: 'login',
        welcomeNew: 'home',
        tourSteps: 'home',
        assessment: 'home',
        result: 'home',
        notifications: 'home',
        legal: legalOrigin,
      }[screen];

      if (screen === 'home' && homeTab !== 'home') {
        setHomeTab('home');
        return true;
      }
      if (!previousScreen) return false;
      setScreen(previousScreen);
      return true;
    });

    return () => subscription.remove();
  }, [screen, homeTab, legalOrigin]);

  useEffect(() => {
    (async () => {
      try {
        const [savedToken, savedLanguage, hasAccount] = await Promise.all([
          AsyncStorage.getItem('matako_token'),
          AsyncStorage.getItem('matako_language'),
          AsyncStorage.getItem('matako_has_account'),
        ]);
        setMonochrome((await AsyncStorage.getItem('matako_monochrome')) === 'true');
        if (savedLanguage && LANGUAGES.includes(savedLanguage)) setLanguage(savedLanguage);
        if (savedToken) {
          setToken(savedToken);
          setScreen('home');
        } else {
          setScreen(hasAccount === 'true' ? 'login' : 'welcome');
        }
      } catch {
        setScreen('welcome');
      }
    })();
  }, []);

  useEffect(() => {
    if (!remindersOn) return undefined;
    const timer = setInterval(() => {
      setRemindersToday((count) => count + 1);
      Alert.alert(t('Eye break'), t('Look 20 feet away for 20 seconds.'));
    }, 20 * 60 * 1000);
    return () => clearInterval(timer);
  }, [remindersOn]);

  useEffect(() => {
    if (!token) return undefined;
    const day = new Date().toDateString();
    AsyncStorage.getItem('matako_screen_time').then((stored) => {
      if (!stored) return;
      const usage = JSON.parse(stored);
      if (usage.day === day) setAppMinutes(usage.minutes || 0);
    }).catch(() => {});
    const timer = setInterval(() => {
      if (AppState.currentState === 'active') setAppMinutes((minutes) => {
        const next = minutes + 1;
        AsyncStorage.setItem('matako_screen_time', JSON.stringify({ day, minutes: next })).catch(() => {});
        return next;
      });
    }, 60 * 1000);
    return () => clearInterval(timer);
  }, [token]);

  useEffect(() => {
    if (screen !== 'home' || !token) return;
    let active = true;
    Promise.all([request('/user', { token }), request('/assessments', { token })])
      .then(([profile, history]) => {
        if (!active) return;
        setUser(profile.user);
        setAssessments(history.assessments || []);
      })
      .catch((error) => {
        if (error.message.includes('Unauthenticated') || error.message.includes('401')) {
          AsyncStorage.removeItem('matako_token');
          setToken(null);
          setScreen('login');
        }
      });
    return () => { active = false; };
  }, [screen, token]);

  const saveLanguage = async (nextLanguage) => {
    setLanguage(nextLanguage);
    await AsyncStorage.setItem('matako_language', nextLanguage);
  };

  const updateProfile = async (profile) => {
    setLoading(true);
    try {
      const data = await request('/user', { token, method: 'PATCH', body: { ...profile, age: Number(profile.age) } });
      setUser(data.user);
      return true;
    } catch (error) {
      Alert.alert(t('Edit Profile'), error.message);
      return false;
    } finally { setLoading(false); }
  };

  const signIn = async ({ email, password }) => {
    setLoading(true);
    try {
      const data = await request('/login', { method: 'POST', body: { email: email.trim(), password } });
      await AsyncStorage.multiSet([['matako_token', data.token], ['matako_has_account', 'true']]);
      setToken(data.token);
      setUser(data.user);
      setScreen('home');
    } catch (error) {
      Alert.alert(t('Sign In'), error.message);
    } finally { setLoading(false); }
  };

  const signUp = async (form) => {
    setLoading(true);
    try {
      const data = await request('/register', {
        method: 'POST',
        body: { ...form, age: Number(form.age), role },
      });
      await AsyncStorage.multiSet([['matako_token', data.token], ['matako_has_account', 'true']]);
      setToken(data.token);
      setUser(data.user);
      setScreen('welcomeNew');
    } catch (error) {
      Alert.alert(t('Create Account'), error.message);
    } finally { setLoading(false); }
  };

  const logOut = async () => {
    if (token) request('/logout', { token, method: 'POST' }).catch(() => {});
    await AsyncStorage.removeItem('matako_token');
    setToken(null);
    setUser(null);
    setAssessments([]);
    setHomeTab('home');
    setScreen('welcome');
  };

  const submitAssessment = async (answers) => {
    setLoading(true);
    try {
      const data = await request('/assessment', { token, method: 'POST', body: { answers } });
      setResult(data);
      setScreen('result');
    } catch (error) {
      Alert.alert(t('Self-assessment'), error.message);
    } finally { setLoading(false); }
  };

  const refreshHistory = async () => {
    try {
      const data = await request('/assessments', { token });
      setAssessments(data.assessments || []);
    } catch (error) { Alert.alert('MataKo', error.message); }
  };

  const openHistoryResult = async (id) => {
    try {
      const data = await request(`/assessment/${id}`, { token });
      setResult(data);
      setScreen('result');
    } catch (error) { Alert.alert('MataKo', error.message); }
  };

  const header = (title, onBack) => (
    <View style={styles.header}>
      {onBack ? <Pressable onPress={onBack} style={styles.back}><Text style={styles.backText}>‹</Text></Pressable> : null}
      <Text style={styles.headerTitle}>{title}</Text>
      {onBack ? <View style={styles.back} /> : null}
    </View>
  );

  const button = (label, onPress, secondary = false, disabled = false) => (
    <Pressable onPress={onPress} disabled={disabled} style={[styles.button, secondary && styles.buttonSecondary, disabled && styles.disabled]}>
      <Text style={[styles.buttonText, secondary && styles.buttonSecondaryText]}>{label}</Text>
    </Pressable>
  );

  const card = (children, style) => <View style={[styles.card, style]}>{children}</View>;

  const renderWelcome = () => (
    <View style={styles.welcomePage}>
      <View style={styles.welcomeBrand}>
        <Image source={require('./assets/images/mata.png')} style={styles.welcomeImage} resizeMode="contain" />
        <Text style={styles.tagline}>{t('YOUR VISION, YOUR POWER.')}</Text>
      </View>
      {button(t('Get Started'), () => setScreen('profile'))}
    </View>
  );

  const renderProfile = () => (
    <>
      {header('MataKo', () => setScreen('welcome'))}
      <ScrollView contentContainerStyle={styles.page}>
        <Text style={styles.title}>{t('Select Your Profile Type')}</Text>
        <Text style={styles.muted}>{t('This will help us tailor your experience.')}</Text>
        {['student', 'professional'].map((item) => (
          <Pressable key={item} onPress={() => setRole(item)} style={[styles.roleCard, role === item && styles.roleSelected]}>
            <Image source={item === 'student' ? require('./assets/images/student.png') : require('./assets/images/employee.png')} style={styles.roleImage} resizeMode="contain" />
            <Text style={styles.roleText}>{t(item === 'student' ? 'Select Student' : 'Select Professional')}</Text>
          </Pressable>
        ))}
        {button(t('Proceed'), () => setScreen('language'))}
        <Pressable onPress={() => setScreen('login')}><Text style={styles.linkCenter}>{t('Skip for now')}</Text></Pressable>
      </ScrollView>
    </>
  );

  const renderLanguage = () => (
    <>
      {header('MataKo', () => setScreen('profile'))}
      <View style={styles.page}>
        <Image source={require('./assets/images/language.png')} style={styles.languageImage} resizeMode="contain" />
        <Text style={styles.title}>{t("Select a language you're most comfortable with.")}</Text>
        <Text style={[styles.muted, { marginTop: 18 }]}>{t('Choose your language:')}</Text>
        {LANGUAGES.map((item) => (
          <Pressable key={item} onPress={() => saveLanguage(item)} style={[styles.languageChoice, item === language && styles.languageSelected]}>
            <Text style={styles.bodyText}>{item === 'English' ? '🇺🇸' : '🇵🇭'}  {item}</Text><Text style={styles.bodyText}>{item === language ? '●' : '○'}</Text>
          </Pressable>
        ))}
        {button(t('Proceed'), () => setScreen('login'))}
        <Pressable onPress={() => { saveLanguage('English'); setScreen('login'); }}><Text style={styles.linkCenter}>{t('Skip for now')}</Text></Pressable>
      </View>
    </>
  );

  const renderLogin = () => <AuthForm mode="login" language={language} onSubmit={signIn} loading={loading} onSignUp={() => setScreen('register')} onBack={() => setScreen('welcome')} onLegal={(tab) => { setLegalTab(tab); setLegalOrigin('login'); setScreen('legal'); }} />;
  const renderRegister = () => <AuthForm mode="register" language={language} role={role} values={registerValues} onValuesChange={setRegisterValues} agreed={legalAccepted} onAgreementChange={setLegalAccepted} onSubmit={signUp} loading={loading} onSignIn={() => setScreen('login')} onBack={() => setScreen('login')} onLegal={(tab) => { setLegalTab(tab); setLegalOrigin('register'); setScreen('legal'); }} />;

  const renderTour = () => (
    <>
      {header(t('Welcome to MataKo!'), () => setScreen('home'))}
      <View style={styles.page}>
        <Image source={require('./assets/images/side.png')} style={styles.tourImage} resizeMode="contain" />
        <Text style={styles.title}>{t('Your personal companion in protecting your eyes from Digital Eye Strain.')}</Text>
        <Text style={[styles.bodyText, { textAlign: 'center', marginVertical: 20 }]}>{t('Take a quick tour of your eye care dashboard, assessment, progress, and healthy screen habits.')}</Text>
        {button(t('Take a Tour'), () => setScreen('tourSteps'))}
        {button(t('Go to Home'), () => setScreen('home'), true)}
      </View>
    </>
  );

  const renderTourSteps = () => (
    <>
      {header(t('Quick Tour'), () => setScreen('home'))}
      <ScrollView contentContainerStyle={styles.page}>
        {card(<><Text style={styles.cardTitle}>{t('1. Check in with your eyes')}</Text><Text style={styles.bodyText}>{t('Answer five questions and get a screening result with practical suggestions.')}</Text></>)}
        {card(<><Text style={styles.cardTitle}>{t('2. Track your progress')}</Text><Text style={styles.bodyText}>{t('Review your saved assessment history from the Progress tab.')}</Text></>)}
        {card(<><Text style={styles.cardTitle}>{t('3. Build healthy habits')}</Text><Text style={styles.bodyText}>{t('Read eye care tips and turn on 20-minute break reminders.')}</Text></>)}
        {button(t('Go to Home'), () => setScreen('home'))}
      </ScrollView>
    </>
  );

  const renderHome = () => <Home
    user={user} assessments={assessments} remindersOn={remindersOn} remindersToday={remindersToday} appMinutes={appMinutes} monochrome={monochrome}
    selectedTab={homeTab} onTab={setHomeTab} onAssessment={() => setScreen('assessment')}
    onNotification={() => setScreen('notifications')} onHistory={openHistoryResult}
    onReminder={setRemindersOn} onMonochrome={(enabled) => { setMonochrome(enabled); AsyncStorage.setItem('matako_monochrome', String(enabled)).catch(() => {}); }} onLogout={logOut} onLanguage={saveLanguage} onSaveProfile={updateProfile} loading={loading}
    language={language} onLegal={(tab) => { setLegalTab(tab); setLegalOrigin('home'); setScreen('legal'); }}
    onRefresh={refreshHistory} t={t} card={card} button={button}
  />;

  const renderAssessment = () => <Assessment onBack={() => setScreen('home')} onSubmit={submitAssessment} loading={loading} t={t} header={header} button={button} card={card} />;

  const renderResult = () => {
    const assessment = result?.assessment || {};
    const risk = assessment.risk_level || 'LOW';
    const riskColor = risk === 'HIGH' ? '#B54708' : risk === 'MEDIUM' ? ORANGE : NAVY;
    return <>
      {header(t('Your result'), () => setScreen('home'))}
      <ScrollView contentContainerStyle={styles.page}>
        <Text style={styles.resultEye}>◉</Text>
        <Text style={[styles.resultRisk, { color: riskColor }]}>{t(risk)}</Text>
        <Text style={styles.bodyText}>{t('Digital eye strain risk · ')}{assessment.total_score || 0}{t(' of 15 points')}</Text>
        {card(<><Text style={styles.cardTitle}>{t('Suggestions for you')}</Text>{(result?.recommendations || []).map((item) => <Text key={item} style={styles.recommendation}>✓  {t(item)}</Text>)}</>)}
        <Text style={styles.muted}>{t('This screening result is not a diagnosis. If symptoms persist or concern you, consider speaking with an eye care professional.')}</Text>
        {button(t('Back to dashboard'), async () => { await refreshHistory(); setScreen('home'); })}
      </ScrollView>
    </>;
  };

  const renderNotifications = () => {
    const todayDate = new Date();
    const yesterdayDate = new Date(todayDate);
    yesterdayDate.setDate(yesterdayDate.getDate() - 1);
    const isSameDay = (value, day) => new Date(value).toDateString() === day.toDateString();
    const todayAssessments = assessments.filter((item) => isSameDay(item.created_at, todayDate));
    const yesterdayAssessments = assessments.filter((item) => isSameDay(item.created_at, yesterdayDate));
    const earlierAssessments = assessments.filter((item) => !isSameDay(item.created_at, todayDate) && !isSameDay(item.created_at, yesterdayDate));
    const notificationCard = ({ icon, title, message, actionLabel, onAction, key }) => <View key={key} style={styles.notificationCard}>
      <View style={styles.notificationIconCircle}><Text style={styles.notificationIcon}>{icon}</Text></View>
      <View style={styles.notificationContent}>
        <Text style={styles.notificationTitle}>{title}</Text>
        <Text style={styles.notificationMessage}>{message}</Text>
        {actionLabel ? <Pressable accessibilityRole="button" onPress={onAction} style={styles.notificationAction}><Text style={styles.notificationActionText}>{actionLabel}  ›</Text></Pressable> : null}
      </View>
    </View>;
    const assessmentNotification = (item) => notificationCard({
      key: `assessment-${item.id}`,
      icon: '✓',
      title: t('Assessment completed'),
      message: `${t('Your result is ready:')} ${t(item.risk_level || 'LOW')} ${t('risk')} (${item.total_score ?? 0} ${t('of 15')}).`,
      actionLabel: t('View result'),
      onAction: () => openHistoryResult(item.id),
    });
    return <View style={styles.fill}>
      <View style={styles.notificationHeader}>
        <Pressable accessibilityRole="button" accessibilityLabel="Back to Home" onPress={() => setScreen('home')} style={styles.notificationBack}>
          <Text style={styles.notificationBackIcon}>‹</Text>
        </Pressable>
        <Text style={styles.notificationHeaderTitle}>{t('Notifications')}</Text>
        <Pressable accessibilityRole="button" accessibilityLabel={showEarlierNotifications ? 'Show recent notifications' : 'Show earlier notifications'} onPress={() => setShowEarlierNotifications((value) => !value)} style={styles.notificationFilter}>
          <Text style={styles.notificationFilterIcon}>≡</Text>
        </Pressable>
      </View>
      <ScrollView contentContainerStyle={styles.notificationList}>
        <Text style={styles.notificationDate}>{t('Today')}</Text>
        {notificationCard({
          key: 'reminder-status',
          icon: '◷',
          title: t('Screen break reminders'),
          message: remindersOn
            ? remindersToday > 0 ? `${remindersToday} ${t(remindersToday === 1 ? 'break reminder has appeared while MataKo was open today.' : 'break reminders have appeared while MataKo was open today.')}` : t('Reminders are enabled. Keep MataKo open to receive your scheduled break prompts.')
            : t('Break reminders are currently turned off. You can enable them in Eye Care.'),
          actionLabel: t(remindersOn ? 'Manage reminders' : 'Set up reminders'),
          onAction: () => { setHomeTab('eyeCare'); setScreen('home'); },
        })}
        {todayAssessments.map(assessmentNotification)}
        {todayAssessments.length === 0 ? <Text style={styles.notificationEmpty}>{t('No self-assessments completed today.')}</Text> : null}
        {yesterdayAssessments.length ? <>
          <Text style={styles.notificationDate}>{t('Yesterday')}</Text>
          {yesterdayAssessments.map(assessmentNotification)}
        </> : null}
        {showEarlierNotifications && earlierAssessments.length ? <>
          <Text style={styles.notificationDate}>{t('Earlier')}</Text>
          {earlierAssessments.map(assessmentNotification)}
        </> : null}
        {showEarlierNotifications && earlierAssessments.length === 0 ? <Text style={styles.notificationEmpty}>{t('There are no earlier assessment updates.')}</Text> : null}
      </ScrollView>
    </View>;
  };

  const renderLegal = () => {
    const consentRequired = legalOrigin === 'register';
    const documents = {
      'Terms of Service': {
        intro: 'Welcome to MataKo, your personal guide to managing Digital Eye Strain (DES). By using MataKo, you agree to abide by the following terms. Please read carefully.',
        sections: [
          ['1. Acceptance of Terms', 'By accessing and using MataKo, you confirm that you are at least 18 years old and agree to be bound by these Terms of Service.'],
          ['2. Purpose of the Service', 'MataKo is designed to help young adults and working professionals self-assess and manage symptoms of Digital Eye Strain through self-evaluation tools, behavior-based strategies, reminders, and educational content.'],
          ['3. User Responsibilities', 'You are responsible for maintaining the confidentiality of your MataKo account and agree to use the app in a lawful and respectful manner. All assessments and feedback are for informational purposes only and do not substitute for medical advice.'],
          ['4. Health Disclaimer', 'MataKo is not intended to diagnose, treat, or replace professional medical advice. Users with ongoing or severe eye symptoms are strongly encouraged to consult a licensed eye care professional.'],
          ['5. Content Usage', 'All content such as ergonomic advice, educational materials, and care suggestions provided in MataKo is protected and may not be reproduced or redistributed without permission.'],
        ],
      },
      'Privacy Policy': {
        intro: 'At MataKo, we value your privacy and are committed to protecting your personal and health-related information.',
        sections: [
          ['1. Information We Collect', 'Personal Information: Name, email (if account is created), and age group.\nHealth Data: Your self-assessment responses (e.g., DES symptoms, screen time habits).\nApp Usage Data: Frequency of feature usage, interactions with reminders and educational tips.'],
          ['2. How We Use Your Data', 'To generate personalized care suggestions and ergonomic guidance.\nTo provide culturally relevant health education.\nTo improve app performance and user experience.\nFor anonymous research purposes to enhance DES prevention tools.'],
          ['3. Data Sharing', 'We do not sell your data. Data may be shared in aggregated, anonymized form for academic or product development purposes. No individual information is ever shared without consent.'],
          ['4. Storage and Security', 'Your data is securely stored and encrypted. We implement industry-standard security measures to protect against unauthorized access.'],
          ['5. User Rights', 'You have the right to:\n• Access your data.\n• Request correction or deletion.\n• Withdraw consent at any time by deleting your account or contacting us.'],
        ],
      },
    };
    const document = documents[legalTab] || documents['Terms of Service'];
    return <View style={styles.legalPage}>
      <View style={styles.legalHeader}>
        <Pressable accessibilityRole="button" accessibilityLabel={consentRequired ? 'Back to Sign Up' : 'Back'} onPress={() => setScreen(legalOrigin)} style={styles.legalBack}>
          <Text style={styles.legalBackIcon}>‹</Text>
        </Pressable>
        <Text style={styles.legalBackLabel}>{t(consentRequired ? 'Back to Sign Up' : 'Back')}</Text>
      </View>
      <View style={styles.legalTabs}>
        {['Terms of Service', 'Privacy Policy'].map((tab) => <Pressable key={tab} onPress={() => setLegalTab(tab)} style={[styles.legalTab, legalTab === tab && styles.legalTabSelected]}>
          <Text style={[styles.legalTabText, legalTab === tab && styles.legalTabTextSelected]}>{t(tab)}</Text>
        </Pressable>)}
      </View>
      <ScrollView style={styles.legalScroll} contentContainerStyle={styles.legalDocument}>
        <Text style={styles.legalTitle}>{t(legalTab)}</Text>
        <Text style={styles.legalParagraph}>{t(document.intro)}</Text>
        {document.sections.map(([heading, paragraph]) => <View key={heading}>
          <Text style={styles.legalSectionTitle}>{t(heading)}</Text>
          <Text style={styles.legalParagraph}>{t(paragraph)}</Text>
        </View>)}
      </ScrollView>
      <View style={styles.legalFooter}>
        {button(t(consentRequired ? 'I Agree and Continue' : 'Done'), () => {
          if (consentRequired) setLegalAccepted(true);
          setScreen(legalOrigin);
        })}
      </View>
    </View>;
  };

  let content;
  if (screen === 'loading') content = <View style={styles.center}><Text style={styles.brandText}>MataKo</Text><Text style={styles.muted}>Loading…</Text></View>;
  else if (screen === 'welcome') content = renderWelcome();
  else if (screen === 'profile') content = renderProfile();
  else if (screen === 'language') content = renderLanguage();
  else if (screen === 'login') content = renderLogin();
  else if (screen === 'register') content = renderRegister();
  else if (screen === 'welcomeNew') content = renderTour();
  else if (screen === 'tourSteps') content = renderTourSteps();
  else if (screen === 'assessment') content = renderAssessment();
  else if (screen === 'result') content = renderResult();
  else if (screen === 'notifications') content = renderNotifications();
  else if (screen === 'legal') content = renderLegal();
  else if (screen.startsWith('home:')) content = renderHome();
  else content = renderHome();

  return <SafeAreaView style={[styles.safe, Platform.OS === 'android' && { paddingTop: Constants.statusBarHeight }]}><LanguageContext.Provider value={language}><StatusBar barStyle="dark-content" backgroundColor={WARM} /><KeyboardAvoidingView style={styles.fill} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>{content}</KeyboardAvoidingView></LanguageContext.Provider></SafeAreaView>;
}

function AuthForm({ mode, language, role, values: savedValues, onValuesChange, agreed = false, onAgreementChange, onSubmit, loading, onSignUp, onSignIn, onBack, onLegal }) {
  const [localValues, setLocalValues] = useState({ name: '', email: '', phone: '', age: '', password: '', password_confirmation: '' });
  const [remember, setRemember] = useState(false);
  const [passwordVisible, setPasswordVisible] = useState(false);
  const [confirmVisible, setConfirmVisible] = useState(false);
  const isRegister = mode === 'register';
  const values = savedValues || localValues;
  const t = (value) => translations[language]?.[value] || value;
  const update = (key, value) => (onValuesChange || setLocalValues)((current) => ({ ...current, [key]: value }));
  const field = (key, label, options = {}) => (
    <View style={styles.field} key={key}>
      <Text style={styles.fieldLabel}>{t(label)}</Text>
      <TextInput
        value={values[key]} onChangeText={(value) => update(key, value)}
        placeholder={translations[language]?.[options.placeholder || label] || options.placeholder || label} placeholderTextColor="#9AA3B2"
        keyboardType={options.keyboardType || 'default'} autoCapitalize={options.autoCapitalize || 'sentences'}
        secureTextEntry={options.secure && !(key === 'password' ? passwordVisible : confirmVisible)}
        style={styles.input} returnKeyType="next"
      />
      {options.secure ? <Pressable onPress={() => key === 'password' ? setPasswordVisible(!passwordVisible) : setConfirmVisible(!confirmVisible)}><Text style={styles.showPassword}>{t((key === 'password' ? passwordVisible : confirmVisible) ? 'Hide' : 'Show')}</Text></Pressable> : null}
    </View>
  );
  const submit = () => {
    if (!values.email.includes('@') || !values.password) { Alert.alert(t('Sign In'), t('Enter a valid email and password.')); return; }
    if (isRegister) {
      if (!values.name.trim() || !values.phone.trim() || Number(values.age) < 18 || Number(values.age) > 120) { Alert.alert(t('Create Account'), t('Complete each field. You must be at least 18 to sign up.')); return; }
      if (values.password.length < 8 || values.password !== values.password_confirmation) { Alert.alert(t('Create Account'), t('Use at least 8 characters and make sure the passwords match.')); return; }
      if (!agreed) { Alert.alert(t('Create Account'), t('Please agree to the Terms and Privacy Policy.')); return; }
    }
    onSubmit(values);
  };
  return <>
    <View style={styles.header}>
      <Pressable onPress={onBack} style={styles.back}><Text style={styles.backText}>‹</Text></Pressable>
      <Text style={styles.headerTitle}>{isRegister ? t('Sign Up') : t('Sign In')}</Text>
      <View style={styles.back} />
    </View>
    <ScrollView keyboardShouldPersistTaps="handled" contentContainerStyle={styles.authPage}>
      <Image source={require('./assets/images/atam.png')} style={styles.authLogo} resizeMode="contain" />
      <Text style={styles.tagline}>{t('YOUR VISION, YOUR POWER.')}</Text>
      <Text style={styles.authSubtitle}>{t(isRegister ? 'Join us and start your journey today' : 'Sign in to your account to continue')}</Text>
      <View style={styles.authCard}>
        {isRegister ? field('name', 'Full Name', { autoCapitalize: 'words' }) : null}
        {field('email', 'Email Address', { keyboardType: 'email-address', autoCapitalize: 'none' })}
        {isRegister ? field('phone', 'Phone Number', { keyboardType: 'phone-pad' }) : null}
        {isRegister ? field('age', 'Age', { keyboardType: 'number-pad' }) : null}
        {field('password', 'Password', { secure: true, autoCapitalize: 'none' })}
        {isRegister ? field('password_confirmation', 'Confirm Password', { secure: true, autoCapitalize: 'none' }) : null}
        {!isRegister ? <View style={styles.inlineRow}>
          <Pressable style={styles.inlineRow} onPress={() => setRemember(!remember)}><Text style={styles.checkbox}>{remember ? '☑' : '□'}</Text><Text style={styles.smallText}>{t('Remember me')}</Text></Pressable>
          <Pressable onPress={() => Alert.alert(t('Forgot Password?'), t('Password reset is not configured yet.'))}><Text style={styles.link}>{t('Forgot Password?')}</Text></Pressable>
        </View> : <View style={[styles.inlineRow, { alignItems: 'flex-start', marginVertical: 8 }]}>
          <Pressable accessibilityRole="checkbox" accessibilityState={{ checked: agreed }} onPress={() => onAgreementChange?.(!agreed)}><Text style={styles.checkbox}>{agreed ? '☑' : '□'}</Text></Pressable>
          <Text style={[styles.smallText, { flex: 1 }]}>{t('I agree to the ')}<Text style={styles.link} onPress={() => onLegal('Terms of Service')}>{t('Terms of Service')}</Text>{t(' and ')}<Text style={styles.link} onPress={() => onLegal('Privacy Policy')}>{t('Privacy Policy')}</Text>.</Text>
        </View>}
        <Pressable onPress={submit} disabled={loading} style={[styles.button, { marginTop: 12 }, loading && styles.disabled]}><Text style={styles.buttonText}>{loading ? (isRegister ? 'Creating account…' : 'Signing in…') : t(isRegister ? 'Create Account' : 'Sign In')}</Text></Pressable>
        <Text style={styles.separator}>────────  <Text>{t('or continue with')}</Text>  ────────</Text>
        <Pressable style={styles.socialButton} onPress={() => Alert.alert(t('Unavailable'), t('Google sign-in is not configured yet.'))}><Text style={styles.bodyText}>◎  {t('Continue with Google')}</Text></Pressable>
        {Platform.OS === 'ios' ? <Pressable style={styles.socialButton} onPress={() => Alert.alert(t('Unavailable'), t('Apple sign-in is not configured yet.'))}><Text style={styles.bodyText}>●  {t('Continue with Apple')}</Text></Pressable> : null}
      </View>
      <Pressable onPress={isRegister ? onSignIn : onSignUp} style={styles.bottomLink}><Text style={styles.bodyText}>{t(isRegister ? 'Already have an account? ' : "Don't have an account? ")}<Text style={styles.link}>{isRegister ? t('Sign In') : t('Sign Up')}</Text></Text></Pressable>
    </ScrollView>
  </>;
}

function FeatureGlyph({ kind }) {
  if (kind === 'assessment') return <View style={styles.clipboardIcon}>
    <View style={styles.clipboardClip} />
    <Text style={styles.clipboardCheck}>✓</Text>
    <View style={styles.clipboardLine} />
    <View style={styles.clipboardLine} />
  </View>;
  if (kind === 'tips') return <View style={styles.heartShape}>
    <View style={styles.heartBody} />
    <View style={styles.heartLobeLeft} />
    <View style={styles.heartLobeRight} />
  </View>;
  if (kind === 'reminders') return <View style={styles.clockIcon}><View style={styles.clockHandLong} /><View style={styles.clockHandShort} /></View>;
  return <View style={styles.gearShape}>
    {[0, 45, 90, 135].map((angle) => <View key={angle} style={[styles.gearBar, { transform: [{ rotate: `${angle}deg` }] }]} />)}
    <View style={styles.gearCore}><View style={styles.gearHole} /></View>
  </View>;
}

function ProfileGlyph({ kind, color }) {
  const stroke = { borderColor: color, borderWidth: 1.5 };
  if (kind === 'email') return <View style={[{ width: 14, height: 10, borderRadius: 2, justifyContent: 'center', overflow: 'hidden' }, stroke]}>
    <View style={{ position: 'absolute', top: 1, left: 1, width: 8, height: 8, borderTopWidth: 1.5, borderRightWidth: 1.5, borderColor: color, transform: [{ rotate: '135deg' }] }} />
  </View>;
  if (kind === 'phone') return <View style={{ width: 15, height: 15, alignItems: 'center', justifyContent: 'center', transform: [{ rotate: '-40deg' }] }}>
    <View style={{ width: 6, height: 11, borderLeftWidth: 2.5, borderRightWidth: 2.5, borderColor: color, borderRadius: 2 }} />
    <View style={{ position: 'absolute', top: 1, width: 7, height: 3, borderTopWidth: 2.5, borderColor: color, borderRadius: 2 }} />
    <View style={{ position: 'absolute', bottom: 1, width: 7, height: 3, borderBottomWidth: 2.5, borderColor: color, borderRadius: 2 }} />
  </View>;
  if (kind === 'calendar') return <View style={[{ width: 14, height: 14, borderRadius: 2, overflow: 'hidden', paddingTop: 4 }, stroke]}>
    <View style={{ position: 'absolute', top: 0, left: 0, right: 0, height: 4, backgroundColor: color }} />
    <View style={{ flexDirection: 'row', justifyContent: 'space-evenly' }}>{[0, 1, 2].map((item) => <View key={item} style={{ width: 2, height: 2, backgroundColor: color, marginTop: 2 }} />)}</View>
    <View style={{ flexDirection: 'row', justifyContent: 'space-evenly', marginTop: 1 }}>{[0, 1, 2].map((item) => <View key={item} style={{ width: 2, height: 2, backgroundColor: color }} />)}</View>
  </View>;
  if (kind === 'briefcase') return <View style={{ width: 15, height: 12, alignItems: 'center', justifyContent: 'flex-end' }}>
    <View style={[{ position: 'absolute', top: 0, width: 6, height: 4, borderTopLeftRadius: 2, borderTopRightRadius: 2, borderWidth: 1.5, borderBottomWidth: 0 }, stroke]} />
    <View style={[{ width: 15, height: 9, borderRadius: 2, justifyContent: 'center' }, stroke]}><View style={{ height: 2, backgroundColor: color }} /></View>
  </View>;
  if (kind === 'bell') return <View style={{ width: 14, height: 15, alignItems: 'center', justifyContent: 'flex-end' }}>
    <View style={{ position: 'absolute', top: 2, width: 11, height: 10, backgroundColor: color, borderTopLeftRadius: 6, borderTopRightRadius: 6, borderBottomLeftRadius: 2, borderBottomRightRadius: 2 }} />
    <View style={{ width: 14, height: 2, backgroundColor: color, borderRadius: 2 }} />
    <View style={{ position: 'absolute', top: 0, width: 4, height: 3, backgroundColor: color, borderRadius: 2 }} />
    <View style={{ position: 'absolute', bottom: -1, width: 3, height: 3, borderRadius: 2, backgroundColor: color }} />
  </View>;
  return <View style={{ width: 16, height: 16, alignItems: 'center', justifyContent: 'center' }}>
    {[0, 45, 90, 135].map((angle) => <View key={angle} style={{ position: 'absolute', width: 16, height: 4, borderRadius: 2, backgroundColor: color, transform: [{ rotate: `${angle}deg` }] }} />)}
    <View style={{ width: 10, height: 10, borderRadius: 5, backgroundColor: color, alignItems: 'center', justifyContent: 'center' }}><View style={{ width: 4, height: 4, borderRadius: 2, backgroundColor: ORANGE }} /></View>
  </View>;
}

function ProfileStatGlyph({ kind }) {
  if (kind === 'eye') return <View style={styles.profileStatEye}>
    <View style={styles.profileStatEyeIris}><View style={styles.profileStatEyePupil} /></View>
  </View>;
  if (kind === 'clock') return <View style={styles.profileStatClock}>
    <View style={styles.profileStatClockHandLong} />
    <View style={styles.profileStatClockHandShort} />
  </View>;
  return <View style={styles.profileStatTrophy}>
    <View style={styles.profileStatTrophyCup} />
    <View style={[styles.profileStatTrophyHandle, styles.profileStatTrophyHandleLeft]} />
    <View style={[styles.profileStatTrophyHandle, styles.profileStatTrophyHandleRight]} />
    <View style={styles.profileStatTrophyStem} />
    <View style={styles.profileStatTrophyBase} />
  </View>;
}

function EyeTipGlyph() {
  return <View style={styles.eyeTipGlyph}>
    <View style={styles.eyeTipBulb}><View style={styles.eyeTipHighlight} /></View>
    <View style={styles.eyeTipBulbNeck} />
    <View style={styles.eyeTipBulbBase} />
  </View>;
}

function Home({ user, assessments, remindersOn, remindersToday, appMinutes, monochrome, selectedTab, onTab, onAssessment, onNotification, onHistory, onReminder, onMonochrome, onLogout, onLanguage, language, onLegal, onRefresh, onSaveProfile, loading, t, card, button }) {
  const [editingProfile, setEditingProfile] = useState(false);
  const [featurePrompt, setFeaturePrompt] = useState(null);
  const [profileDraft, setProfileDraft] = useState({ name: '', email: '', phone: '', age: '', role: 'student' });
  const chooseTab = (name) => onTab(name);
  const confirmAssessmentStart = () => setFeaturePrompt('assessment');
  const confirmTipsOpen = () => setFeaturePrompt('tips');
  const confirmRemindersOpen = () => setFeaturePrompt('reminders');
  const confirmSettingsOpen = () => setFeaturePrompt('settings');
  const continueFromFeaturePrompt = () => {
    const nextFeature = featurePrompt;
    setFeaturePrompt(null);
    if (nextFeature === 'assessment') onAssessment();
    if (nextFeature === 'tips') chooseTab('tips');
    if (nextFeature === 'reminders') chooseTab('eyeCare');
    if (nextFeature === 'settings') chooseTab('settings');
  };
  const rows = assessments || [];
  const today = rows.filter((item) => new Date(item.created_at).toDateString() === new Date().toDateString()).length;
  const appTimeLabel = appMinutes < 60 ? `${appMinutes}m` : `${Math.floor(appMinutes / 60)}h ${appMinutes % 60}m`;
  const latest = rows[0];
  const latestRisk = String(latest?.risk_level || 'LOW').toUpperCase();
  const assessmentDays = new Set(rows.map((item) => new Date(item.created_at).toDateString()));
  let streakDays = 0;
  const streakDate = new Date();
  if (!assessmentDays.has(streakDate.toDateString())) streakDate.setDate(streakDate.getDate() - 1);
  while (assessmentDays.has(streakDate.toDateString())) {
    streakDays += 1;
    streakDate.setDate(streakDate.getDate() - 1);
  }
  const openProfileEditor = () => {
    setProfileDraft({ name: user?.name || '', email: user?.email || '', phone: user?.phone || '', age: String(user?.age || ''), role: user?.role || 'student' });
    setEditingProfile(true);
  };
  const saveProfile = async () => {
    const saved = await onSaveProfile(profileDraft);
    if (saved) setEditingProfile(false);
  };
  const profileInfoRow = (kind, label, value) => <View key={label} style={styles.profileInfoRow}>
    <View style={styles.profileInfoIconBox}><ProfileGlyph kind={({ Email: 'email', Phone: 'phone', Age: 'calendar', 'User Type': 'briefcase' })[label] || kind} color="#475467" /></View>
    <View style={styles.profileInfoText}>
      <Text style={styles.profileInfoLabel}>{t(label)}</Text>
      <Text style={styles.profileInfoValue} numberOfLines={1}>{value || 'Not provided'}</Text>
    </View>
  </View>;
  const timeAgo = (value) => {
    const minutes = Math.max(1, Math.floor((Date.now() - new Date(value).getTime()) / 60000));
    if (minutes < 60) return `${minutes} ${t(minutes === 1 ? 'minute' : 'minutes')} ${t('ago')}`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours} ${t(hours === 1 ? 'hour' : 'hours')} ${t('ago')}`;
    const days = Math.floor(hours / 24);
    return `${days} ${t(days === 1 ? 'day' : 'days')} ${t('ago')}`;
  };
  const feature = (kind, title, subtitle, onPress) => <Pressable accessibilityRole="button" onPress={onPress} style={styles.featureCard}>
    <View style={styles.featureIconCircle}><FeatureGlyph kind={kind} /></View>
    <View style={styles.featureCopy}><Text style={styles.featureTitle}>{t(title)}</Text><Text style={styles.featureSubtitle}>{t(subtitle)}</Text></View>
    <Text style={styles.featureArrow}>›</Text>
  </Pressable>;
  const content = selectedTab === 'home' ? (
    <ScrollView contentContainerStyle={styles.homePage} refreshControl={<RefreshControl refreshing={false} onRefresh={onRefresh} tintColor={ORANGE} />}>
      {card(<View style={styles.greeting}><View style={styles.flex}><Text style={styles.greetingTitle}>{t('Hi, ')}{user?.name?.split(' ')[0] || t('User')}!</Text><Text style={styles.bodyText}>{t('Ready to take care of your eyes today?')}</Text></View><Image source={require('./assets/images/student.png')} style={styles.greetingImage} resizeMode="contain" /></View>, styles.greetingCard)}
      {feature('assessment', 'Start Self-Assessment', 'Quick check-up for your eye health', confirmAssessmentStart)}
      {feature('tips', 'View Care Tips', 'Learn how to protect your vision', confirmTipsOpen)}
      {feature('reminders', 'Set Screen Break Reminders', 'Schedule healthy breaks from screens', confirmRemindersOpen)}
      {feature('settings', 'Eye Care Settings', 'Manage your device preferences for your eye care', confirmSettingsOpen)}
      {card(<><Text style={styles.cardTitle}>{t("Today's Progress")}</Text><View style={styles.statRow}><Text style={styles.stat}>{remindersToday}{'\n'}<Text style={styles.smallText}>{t('Screen breaks')}</Text></Text><Text style={styles.stat}>{appMinutes < 60 ? `${appMinutes}m` : `${Math.floor(appMinutes / 60)}h ${appMinutes % 60}m`}{'\n'}<Text style={styles.smallText}>{t('Time in MataKo')}</Text></Text></View></>)}
      {card(<View style={styles.tipRow}><View style={styles.tipIconCircle}><EyeTipGlyph /></View><View style={styles.flex}><Text style={styles.cardTitle}>{t("Today's Eye Tip")}</Text><Text style={styles.bodyText}>{t('Remember to blink more often while using your screen. The 30-30-30 rule can help reduce eye strain.')}</Text></View></View>)}
      {card(<><Text style={styles.cardTitle}>{t('Recent Activity')}</Text>{latest ? <View style={styles.activityRow}>
        <View style={styles.activityIconCircle}><Text style={styles.activityIcon}>✓</Text></View>
        <View style={styles.activityCopy}><Text style={styles.activityTitle}>{t('Self-Assessment Completed')}</Text><Text style={styles.smallText}>{timeAgo(latest.created_at)}</Text></View>
        <Pressable accessibilityRole="button" onPress={confirmAssessmentStart} style={styles.retakeButton}><Text style={styles.retakeText}>{t('Retake')}</Text></Pressable>
      </View> : <Text style={styles.bodyText}>{t('No recent activity yet. Start a self-assessment to see your results here.')}</Text>}
      {remindersToday > 0 ? <View style={styles.activityRow}><View style={styles.activityIconCircle}><ProfileStatGlyph kind="clock" /></View><View style={styles.activityCopy}><Text style={styles.activityTitle}>{t('Break Reminder')}</Text><Text style={styles.smallText}>{remindersToday} {t('today')}</Text></View></View> : null}</>)}
    </ScrollView>
  ) : selectedTab === 'progress' ? (
    <ScrollView contentContainerStyle={styles.page}>
      <Text style={styles.title}>{t('Assessment History')}</Text>
      {rows.length ? rows.map((item) => <Pressable key={item.id} onPress={() => onHistory(item.id)}>{card(<><Text style={styles.cardTitle}>{t(item.risk_level || 'LOW')} {t('risk')} · {item.total_score}/15</Text><Text style={styles.muted}>{new Date(item.created_at).toLocaleString(language === 'Filipino' ? 'fil-PH' : language === 'Cebuano' ? 'ceb-PH' : 'en-US')}</Text></>)}</Pressable>) : card(<Text style={styles.bodyText}>Complete a self-assessment to see your progress here.</Text>)}
      {button(t('Start Self-Assessment'), confirmAssessmentStart)}
    </ScrollView>
  ) : selectedTab === 'eyeCare' ? (
    <ScrollView contentContainerStyle={styles.page}>
      <Text style={styles.title}>{t('Screen break reminders')}</Text>
      {card(<><Text style={styles.cardTitle}>{t('20-20-20 break reminder')}</Text><Text style={styles.bodyText}>{t('Get an in-app reminder to look 20 feet away for 20 seconds every 20 minutes.')}</Text><View style={styles.switchRow}><Text style={styles.bodyText}>{t(remindersOn ? 'Reminder on' : 'Reminder off')}</Text><Switch value={remindersOn} onValueChange={onReminder} trackColor={{ true: ORANGE }} /></View></>)}
      {card(<><Text style={styles.cardTitle}>{t('Everyday eye care')}</Text><Text style={styles.bodyText}>{t('Take regular breaks, blink often, and keep your screen at a comfortable distance.')}</Text></>)}
    </ScrollView>
  ) : selectedTab === 'tips' ? (
    <ScrollView contentContainerStyle={styles.page}>
      <Text style={styles.title}>{t('Eye care tips')}</Text>
      {['Follow the 20-20-20 rule', 'Adjust screen brightness to match your surroundings', 'Blink often to keep your eyes comfortable', 'Keep a comfortable distance from your screen', 'Take regular breaks and stretch'].map((tip, index) => <View key={tip}>{card(<><Text style={styles.cardTitle}>{index + 1}. {t(tip)}</Text><Text style={styles.bodyText}>{t('Small, regular habits can help reduce digital eye strain.')}</Text></>)}</View>)}
    </ScrollView>
  ) : (
    <ScrollView contentContainerStyle={styles.profilePage}>
      {card(<View style={styles.profileSummary}>
        <View style={styles.profileAvatarWrap}>
          <View style={styles.profileAvatar}><Text style={styles.profileAvatarText}>{user?.name?.split(/\s+/).map((part) => part[0]).slice(0, 2).join('').toUpperCase() || 'U'}</Text></View>
          <Pressable accessibilityRole="button" accessibilityLabel="Edit profile" onPress={openProfileEditor} style={styles.profileCamera}>
            <View style={styles.profileCameraIcon}><View style={styles.profileCameraTop} /><View style={styles.profileCameraBody}><View style={styles.profileCameraLens} /></View></View>
          </Pressable>
        </View>
        <Text style={styles.profileName}>{user?.name || 'MataKo User'}</Text>
        <Text style={styles.profileEmail} numberOfLines={1}>{user?.email || ''}</Text>
        <View style={styles.profileStats}>
          {[["eye", 'Screen Time', appTimeLabel], ["clock", 'Breaks', String(remindersToday)], ["trophy", 'Streak', `${streakDays} ${t(streakDays === 1 ? 'day' : 'days')}`]].map(([icon, label, value]) => <View key={label} style={styles.profileStat}>
            <View style={styles.profileStatIconBox}><ProfileStatGlyph kind={icon} /></View>
            <Text style={styles.profileStatLabel}>{t(label)}</Text><Text style={styles.profileStatValue}>{label === 'Screen Time' || label === 'Breaks' ? `${value} ${t('today')}` : value}</Text>
          </View>)}
        </View>
        <Pressable accessibilityRole="button" onPress={openProfileEditor} style={styles.editProfileButton}><Text style={styles.editProfileText}>{t('Edit Profile')}</Text></Pressable>
      </View>, styles.profileSummaryCard)}
      {card(<>
        <View style={styles.profileSectionHeader}><Text style={styles.profileSectionTitle}>{t('Personal Information')}</Text><Pressable accessibilityRole="button" accessibilityLabel="Edit personal information" onPress={openProfileEditor}><Text style={styles.profileEditIcon}>✎</Text></Pressable></View>
        {profileInfoRow('✉', 'Email', user?.email)}
        {profileInfoRow('☎', 'Phone', user?.phone)}
        {profileInfoRow('▦', 'Age', user?.age ? `${user.age} ${t('years')}` : '')}
        {profileInfoRow('▣', 'User Type', t(user?.role === 'professional' ? 'Professional' : 'Student'))}
      </>, styles.profileInfoCard)}
      {card(<>
        <View style={styles.profileSectionHeader}><Text style={styles.profileSectionTitle}>{t('Eye Care Settings')}</Text><Pressable accessibilityRole="button" onPress={() => chooseTab('eyeCare')}><Text style={styles.profileMoreLink}>{t('More eye care settings')}  ›</Text></Pressable></View>
        <View style={styles.profileReminderRow}>
          <View style={styles.profileReminderIcon}><ProfileGlyph kind="bell" color={WHITE} /></View>
          <View style={styles.profileInfoText}><Text style={styles.profileInfoValue}>{t('Break Reminders')}</Text><Text style={styles.profileInfoLabel}>{t('Every 20 minutes')}</Text></View>
          <Switch value={remindersOn} onValueChange={onReminder} trackColor={{ true: ORANGE }} />
        </View>
        <View style={styles.profileReminderRow}>
          <View style={styles.profileReminderIcon}><ProfileGlyph kind="gear" color={WHITE} /></View>
          <View style={styles.profileInfoText}><Text style={styles.profileInfoValue}>{t('Monochrome')}</Text><Text style={styles.profileInfoLabel}>{t(monochrome ? 'Grayscale mode on' : 'Grayscale mode')}</Text></View>
          <Switch accessibilityLabel="Monochrome grayscale mode" value={monochrome} onValueChange={onMonochrome} trackColor={{ true: ORANGE }} />
        </View>
      </>, styles.profileInfoCard)}
      {card(<>
        <Text style={styles.profileSectionTitle}>{t('Latest Assessment')}</Text>
        {latest ? <>
          <Pressable accessibilityRole="button" onPress={() => onHistory(latest.id)} style={styles.latestAssessmentPanel}>
            <View style={styles.latestAssessmentHeading}>
              <Text style={styles.latestAssessmentTitle}>{t(`${latestRisk === 'HIGH' ? 'High' : latestRisk === 'MEDIUM' ? 'Moderate' : 'Mild'} Eye Strain`)}</Text>
              <View style={[styles.latestAssessmentDot, { backgroundColor: latestRisk === 'HIGH' ? '#D92D20' : latestRisk === 'MEDIUM' ? ORANGE : '#12B76A' }]} />
            </View>
            <Text style={styles.latestAssessmentDate}>Assessed on {new Date(latest.created_at).toLocaleDateString(language === 'Filipino' ? 'fil-PH' : language === 'Cebuano' ? 'ceb-PH' : 'en-US', { month: 'long', day: 'numeric', year: 'numeric' })}</Text>
            <Text style={styles.latestAssessmentSummary}>{t(latestRisk === 'HIGH' ? 'Your assessment shows significant symptoms. Consider taking regular breaks and adjusting your screen habits.' : latestRisk === 'MEDIUM' ? 'Your assessment shows moderate symptoms. Consider taking more frequent breaks and adjusting screen brightness.' : 'Your assessment shows mild symptoms. Keep practicing healthy screen habits and taking regular breaks.')}</Text>
          </Pressable>
          <Pressable accessibilityRole="button" onPress={() => chooseTab('progress')} style={styles.latestAssessmentButton}><Text style={styles.latestAssessmentButtonText}>View All Results</Text></Pressable>
        </> : <Text style={styles.profileInfoLabel}>{t('Complete a self-assessment to see your latest result here.')}</Text>}
      </>, styles.profileInfoCard)}
      {card(<><Text style={styles.profileSectionTitle}>{t('Language')}</Text><View style={styles.wrapRow}>{LANGUAGES.map((item) => <Pressable key={item} onPress={() => onLanguage(item)} style={[styles.languagePill, language === item && styles.languageSelected]}><Text style={styles.bodyText}>{item}</Text></Pressable>)}</View></>, styles.profileInfoCard)}
      {card(<><Pressable onPress={() => onLegal('Terms of Service')}><Text style={styles.link}>Terms of Service</Text></Pressable><Pressable onPress={() => onLegal('Privacy Policy')} style={{ marginTop: 12 }}><Text style={styles.link}>Privacy Policy</Text></Pressable></>, styles.profileInfoCard)}
      {button(t('Log out'), onLogout, true)}
      <Modal visible={editingProfile} transparent animationType="slide" onRequestClose={() => setEditingProfile(false)}>
        <View style={styles.profileModalBackdrop}><View style={styles.profileModal}>
          <View style={styles.profileSectionHeader}><Text style={styles.profileSectionTitle}>Edit Profile</Text><Pressable onPress={() => setEditingProfile(false)}><Text style={styles.profileEditIcon}>×</Text></Pressable></View>
          {[
            ['name', 'Full Name'], ['email', 'Email'], ['phone', 'Phone'], ['age', 'Age'],
          ].map(([key, label]) => <View key={key} style={styles.profileEditField}><Text style={styles.profileInfoLabel}>{label}</Text><TextInput value={profileDraft[key]} onChangeText={(value) => setProfileDraft((current) => ({ ...current, [key]: value }))} keyboardType={key === 'email' ? 'email-address' : key === 'phone' ? 'phone-pad' : key === 'age' ? 'number-pad' : 'default'} autoCapitalize={key === 'email' ? 'none' : 'sentences'} style={styles.profileEditInput} /></View>)}
          <Text style={styles.profileInfoLabel}>User Type</Text><View style={styles.wrapRow}>{['student', 'professional'].map((item) => <Pressable key={item} onPress={() => setProfileDraft((current) => ({ ...current, role: item }))} style={[styles.languagePill, profileDraft.role === item && styles.languageSelected]}><Text style={styles.bodyText}>{item === 'student' ? 'Student' : 'Professional'}</Text></Pressable>)}</View>
          <Pressable accessibilityRole="button" onPress={saveProfile} disabled={loading} style={[styles.button, loading && styles.disabled]}><Text style={styles.buttonText}>{loading ? 'Saving…' : 'Save Changes'}</Text></Pressable>
        </View></View>
      </Modal>
    </ScrollView>
  );
  return <View style={styles.fill}>
    {selectedTab === 'settings' ? <View style={styles.profileHeader}>
      <Pressable accessibilityRole="button" accessibilityLabel="Back to Home" onPress={() => chooseTab('home')} style={styles.profileHeaderAction}><Text style={styles.profileHeaderBack}>‹</Text></Pressable>
      <Text style={styles.profileHeaderTitle}>{t('Profile')}</Text>
      <Pressable accessibilityRole="button" accessibilityLabel={t('Profile options')} onPress={() => Alert.alert(t('Profile options'), t('Choose an account action.'), [{ text: t('Log out'), style: 'destructive', onPress: onLogout }, { text: t('Cancel'), style: 'cancel' }])} style={styles.profileHeaderAction}><Text style={styles.profileMenu}>⋮</Text></Pressable>
    </View> : <View style={styles.homeHeader}>
      <Image accessibilityLabel="MataKo" source={require('./assets/images/atam.png')} style={styles.homeLogo} resizeMode="contain" />
      <View style={styles.inlineRow}>
        <Pressable accessibilityRole="button" accessibilityLabel="Notifications" hitSlop={8} onPress={onNotification} style={styles.headerAction}>
          <Text style={styles.headerIcon}>🔔</Text>
        </Pressable>
        <Pressable accessibilityRole="button" accessibilityLabel="Profile and settings" hitSlop={8} onPress={() => chooseTab('settings')} style={styles.avatarButton}>
          <Text style={styles.avatar}>{user?.name?.[0]?.toUpperCase() || 'U'}</Text>
        </Pressable>
      </View>
    </View>}
    {content}
    <View style={styles.bottomNav}>{[['home', '⌂', 'Home'], ['progress', '▤', 'Progress'], ['eyeCare', '◉', 'Eye Care'], ['tips', '♡', 'Tips'], ['settings', '⚙', 'Settings']].map(([key, icon, label]) => <Pressable key={key} style={styles.navItem} onPress={() => chooseTab(key)}><Text style={[styles.navIcon, selectedTab === key && styles.navSelected]}>{icon}</Text><Text style={[styles.navLabel, selectedTab === key && styles.navSelected]}>{t(label)}</Text></Pressable>)}</View>
    <Modal visible={featurePrompt !== null} transparent animationType="fade" statusBarTranslucent onRequestClose={() => setFeaturePrompt(null)}>
      <View style={styles.assessmentPromptBackdrop}>
        <View style={styles.assessmentPrompt}>
          <Text style={styles.assessmentPromptTitle}>{t(featurePrompt === 'tips' ? 'Ready to learn how to take care of your eyes?' : featurePrompt === 'reminders' ? 'Want to take healthy screen breaks?' : featurePrompt === 'settings' ? 'Customize your eye care experience.' : 'Ready to check your eye health?')}</Text>
          <Text style={styles.assessmentPromptMessage}>{t(featurePrompt === 'tips' ? "We've prepared some simple tips to help reduce eye strain." : featurePrompt === 'reminders' ? 'Set gentle reminders to protect your eyes during long screen use.' : featurePrompt === 'settings' ? 'You can enable features like dark mode, dimmers, and pop-up breaks.' : 'This quick self-assessment will only take a few minutes.')}</Text>
          <View style={styles.assessmentPromptActions}>
            <Pressable accessibilityRole="button" onPress={() => setFeaturePrompt(null)} style={styles.assessmentPromptCancel}><Text style={styles.assessmentPromptCancelText}>{t('Cancel')}</Text></Pressable>
            <Pressable accessibilityRole="button" onPress={continueFromFeaturePrompt} style={styles.assessmentPromptNext}><Text style={styles.assessmentPromptNextText}>{t(featurePrompt === 'settings' ? 'Open' : 'Next')}</Text></Pressable>
          </View>
        </View>
      </View>
    </Modal>
  </View>;
}

function Assessment({ onBack, onSubmit, loading, t, header, button, card }) {
  const [answers, setAnswers] = useState(Object.fromEntries(SYMPTOMS.map((symptom) => [symptom, 0])));
  return <>
    {header(t('Self-assessment'), onBack)}
    <ScrollView contentContainerStyle={styles.page}>
      <Text style={styles.bodyText}>Over the past week, how often have you experienced each symptom?</Text>
      {SYMPTOMS.map((symptom) => card(<><Text style={styles.cardTitle}>{t(symptom)}</Text><View style={styles.wrapRow}>{FREQUENCIES.map((frequency, value) => <Pressable key={frequency} onPress={() => setAnswers((current) => ({ ...current, [symptom]: value }))} style={[styles.frequency, answers[symptom] === value && styles.frequencySelected]}><Text style={[styles.frequencyText, answers[symptom] === value && styles.frequencyTextSelected]}>{t(frequency)}</Text></Pressable>)}</View></>))}
      {button(loading ? 'Saving…' : t('See my result'), () => onSubmit(answers), false, loading)}
    </ScrollView>
  </>;
}

const styles = StyleSheet.create({
  profileCameraIcon: { width: 16, height: 14, alignItems: 'center', justifyContent: 'flex-end' }, profileCameraTop: { position: 'absolute', top: 0, left: 3, width: 6, height: 3, borderTopLeftRadius: 2, borderTopRightRadius: 2, backgroundColor: WHITE }, profileCameraBody: { width: 15, height: 10, borderRadius: 2, backgroundColor: WHITE, alignItems: 'center', justifyContent: 'center' }, profileCameraLens: { width: 5, height: 5, borderRadius: 3, backgroundColor: NAVY },
  heartShape: { width: 22, height: 20, position: 'relative' }, heartBody: { width: 13, height: 13, position: 'absolute', top: 5, left: 4.5, backgroundColor: ORANGE, transform: [{ rotate: '45deg' }] }, heartLobeLeft: { width: 12, height: 12, position: 'absolute', top: 1, left: 2, borderRadius: 6, backgroundColor: ORANGE }, heartLobeRight: { width: 12, height: 12, position: 'absolute', top: 1, right: 2, borderRadius: 6, backgroundColor: ORANGE }, gearShape: { width: 22, height: 22, alignItems: 'center', justifyContent: 'center' }, gearBar: { position: 'absolute', width: 22, height: 6, borderRadius: 2, backgroundColor: ORANGE }, gearCore: { width: 14, height: 14, borderRadius: 7, alignItems: 'center', justifyContent: 'center', backgroundColor: ORANGE }, gearHole: { width: 6, height: 6, borderRadius: 3, backgroundColor: WHITE },
  assessmentPromptBackdrop: { flex: 1, backgroundColor: '#0006', alignItems: 'center', justifyContent: 'center', padding: 24 }, assessmentPrompt: { width: '74%', maxWidth: 300, backgroundColor: WHITE, borderRadius: 8, padding: 12, elevation: 12 }, assessmentPromptTitle: { color: NAVY, fontSize: 13, fontWeight: '700', marginBottom: 8 }, assessmentPromptMessage: { color: '#555555', fontSize: 11, lineHeight: 15 }, assessmentPromptActions: { flexDirection: 'row', gap: 6, marginTop: 14 }, assessmentPromptCancel: { flex: 1, minHeight: 30, backgroundColor: '#F4F4F4', borderRadius: 9, borderWidth: 1, borderColor: '#D8D8D8', alignItems: 'center', justifyContent: 'center' }, assessmentPromptCancelText: { color: NAVY, fontSize: 10 }, assessmentPromptNext: { flex: 1, minHeight: 30, backgroundColor: ORANGE, borderRadius: 9, alignItems: 'center', justifyContent: 'center' }, assessmentPromptNextText: { color: WHITE, fontSize: 10, fontWeight: '700' },
  profilePage: { paddingHorizontal: 14, paddingTop: 12, paddingBottom: 28 }, profileHeader: { height: 52, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: WHITE, paddingHorizontal: 12 }, profileHeaderAction: { width: 42, height: 48, alignItems: 'center', justifyContent: 'center' }, profileHeaderBack: { color: '#344054', fontSize: 34, lineHeight: 40 }, profileHeaderTitle: { color: NAVY, fontSize: 15, fontWeight: '700' }, profileMenu: { color: NAVY, fontSize: 24 }, profileSummaryCard: { padding: 12 }, profileSummary: { alignItems: 'center' }, profileAvatarWrap: { width: 82, height: 82, marginBottom: 5 }, profileAvatar: { width: 76, height: 76, borderRadius: 38, borderWidth: 3, borderColor: NAVY, backgroundColor: '#DDE7EA', alignItems: 'center', justifyContent: 'center' }, profileAvatarText: { color: NAVY, fontSize: 24, fontWeight: '700' }, profileCamera: { position: 'absolute', right: 0, bottom: 0, width: 27, height: 27, borderRadius: 14, backgroundColor: NAVY, borderWidth: 2, borderColor: WHITE, alignItems: 'center', justifyContent: 'center' }, profileCameraText: { color: WHITE, fontSize: 14 }, profileName: { color: NAVY, fontSize: 16, fontWeight: '700', marginTop: 2 }, profileEmail: { color: '#87909C', fontSize: 11, marginTop: 2, maxWidth: '95%' }, profileStats: { flexDirection: 'row', justifyContent: 'space-around', width: '100%', marginTop: 14, marginBottom: 14 }, profileStat: { flex: 1, alignItems: 'center' }, profileStatIconBox: { width: 69, height: 40, borderRadius: 10, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', marginBottom: 5 }, profileStatEye: { width: 18, height: 12, borderWidth: 2, borderColor: WHITE, borderRadius: 10, alignItems: 'center', justifyContent: 'center' }, profileStatEyeIris: { width: 7, height: 7, borderRadius: 4, backgroundColor: WHITE, alignItems: 'center', justifyContent: 'center' }, profileStatEyePupil: { width: 3, height: 3, borderRadius: 2, backgroundColor: ORANGE }, profileStatClock: { width: 16, height: 16, borderRadius: 8, backgroundColor: WHITE }, profileStatClockHandLong: { position: 'absolute', width: 2, height: 5, top: 3, left: 7, borderRadius: 1, backgroundColor: ORANGE }, profileStatClockHandShort: { position: 'absolute', width: 4, height: 2, top: 7, left: 7, borderRadius: 1, backgroundColor: ORANGE }, profileStatTrophy: { width: 20, height: 20, alignItems: 'center', justifyContent: 'flex-start' }, profileStatTrophyCup: { width: 12, height: 9, backgroundColor: WHITE, borderBottomLeftRadius: 5, borderBottomRightRadius: 5 }, profileStatTrophyHandle: { position: 'absolute', top: 1, width: 5, height: 6, borderWidth: 2, borderColor: WHITE }, profileStatTrophyHandleLeft: { left: 0, borderRightWidth: 0, borderTopLeftRadius: 4, borderBottomLeftRadius: 4 }, profileStatTrophyHandleRight: { right: 0, borderLeftWidth: 0, borderTopRightRadius: 4, borderBottomRightRadius: 4 }, profileStatTrophyStem: { width: 3, height: 4, backgroundColor: WHITE }, profileStatTrophyBase: { width: 12, height: 2, borderRadius: 2, backgroundColor: WHITE }, profileStatLabel: { color: NAVY, fontSize: 10, fontWeight: '600' }, profileStatValue: { color: '#7E8792', fontSize: 9, marginTop: 2 }, editProfileButton: { minHeight: 36, width: '100%', borderRadius: 7, backgroundColor: NAVY, alignItems: 'center', justifyContent: 'center' }, editProfileText: { color: WHITE, fontSize: 11, fontWeight: '600' }, profileInfoCard: { padding: 15 }, profileSectionHeader: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 8 }, profileSectionTitle: { color: NAVY, fontSize: 14, fontWeight: '700' }, profileEditIcon: { color: ORANGE, fontSize: 20, fontWeight: '700' }, profileInfoRow: { minHeight: 46, flexDirection: 'row', alignItems: 'center', marginTop: 4 }, profileInfoIconBox: { width: 27, height: 30, borderRadius: 6, backgroundColor: '#F6F7F9', alignItems: 'center', justifyContent: 'center', marginRight: 9 }, profileInfoIcon: { color: '#475467', fontSize: 14 }, profileInfoText: { flex: 1 }, profileInfoLabel: { color: '#77808B', fontSize: 10 }, profileInfoValue: { color: NAVY, fontSize: 12, marginTop: 2 }, profileMoreLink: { color: ORANGE, fontSize: 9 }, latestAssessmentPanel: { backgroundColor: '#FAFAFA', borderRadius: 8, padding: 12, marginTop: 12, marginBottom: 10 }, latestAssessmentHeading: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, latestAssessmentTitle: { color: NAVY, fontSize: 14, fontWeight: '700', flex: 1 }, latestAssessmentDot: { width: 9, height: 9, borderRadius: 5, marginLeft: 8 }, latestAssessmentDate: { color: '#667085', fontSize: 11, marginTop: 7 }, latestAssessmentSummary: { color: '#555555', fontSize: 11, lineHeight: 16, marginTop: 9 }, latestAssessmentButton: { minHeight: 42, borderRadius: 7, backgroundColor: NAVY, alignItems: 'center', justifyContent: 'center', marginTop: 2 }, latestAssessmentButtonText: { color: WHITE, fontSize: 12, fontWeight: '700' }, profileReminderRow: { flexDirection: 'row', alignItems: 'center', paddingTop: 5 }, profileReminderIcon: { width: 24, height: 28, borderRadius: 5, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', marginRight: 9 }, profileReminderIconText: { color: WHITE, fontSize: 16 }, profileModalBackdrop: { flex: 1, backgroundColor: '#0008', justifyContent: 'flex-end' }, profileModal: { backgroundColor: WHITE, padding: 20, borderTopLeftRadius: 20, borderTopRightRadius: 20, maxHeight: '90%' }, profileEditField: { marginBottom: 10 }, profileEditInput: { minHeight: 42, borderRadius: 8, borderWidth: 1, borderColor: '#D0D5DD', paddingHorizontal: 10, marginTop: 4, color: NAVY },
  safe: { flex: 1, backgroundColor: WARM }, fill: { flex: 1 }, center: { flex: 1, alignItems: 'center', justifyContent: 'center', backgroundColor: WARM },
  legalPage: { flex: 1, backgroundColor: WARM }, legalHeader: { height: 52, flexDirection: 'row', alignItems: 'center', backgroundColor: WHITE, paddingHorizontal: 12 }, legalBack: { width: 40, height: 48, justifyContent: 'center' }, legalBackIcon: { color: '#475467', fontSize: 34, lineHeight: 40 }, legalBackLabel: { color: '#1D2939', fontSize: 14, fontWeight: '500' }, legalTabs: { height: 48, flexDirection: 'row', backgroundColor: '#F1EEEE', borderBottomWidth: 1, borderBottomColor: '#D8D4D2' }, legalTab: { flex: 1, alignItems: 'center', justifyContent: 'center', borderBottomWidth: 2, borderBottomColor: 'transparent' }, legalTabSelected: { borderBottomColor: ORANGE }, legalTabText: { color: '#777777', fontSize: 13 }, legalTabTextSelected: { color: NAVY }, legalScroll: { flex: 1 }, legalDocument: { paddingHorizontal: 16, paddingTop: 20, paddingBottom: 24 }, legalTitle: { color: NAVY, fontSize: 16, fontWeight: '700', marginBottom: 12 }, legalSectionTitle: { color: NAVY, fontSize: 14, fontWeight: '500', marginTop: 18, marginBottom: 8 }, legalParagraph: { color: '#666666', fontSize: 11, lineHeight: 18 }, legalFooter: { backgroundColor: WHITE, paddingHorizontal: 28, paddingTop: 12, paddingBottom: 14 },
  notificationHeader: { height: 52, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: WHITE, paddingHorizontal: 12 }, notificationBack: { width: 42, height: 48, justifyContent: 'center' }, notificationBackIcon: { color: '#475467', fontSize: 34, lineHeight: 40 }, notificationHeaderTitle: { color: NAVY, fontSize: 15, fontWeight: '700' }, notificationFilter: { width: 42, height: 48, alignItems: 'center', justifyContent: 'center' }, notificationFilterIcon: { color: '#777777', fontSize: 23 }, notificationList: { paddingHorizontal: 16, paddingTop: 8, paddingBottom: 24 }, notificationDate: { color: '#777777', fontSize: 11, textAlign: 'center', paddingVertical: 10 }, notificationCard: { flexDirection: 'row', alignItems: 'flex-start', backgroundColor: WHITE, borderRadius: 14, borderWidth: 1, borderColor: '#E5E7EA', padding: 12, marginVertical: 5, elevation: 1 }, notificationIconCircle: { width: 30, height: 30, borderRadius: 15, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', marginRight: 9 }, notificationIcon: { color: WHITE, fontSize: 17, fontWeight: '700' }, notificationContent: { flex: 1 }, notificationTitle: { color: NAVY, fontSize: 12, fontWeight: '700', marginTop: 2 }, notificationMessage: { color: '#555555', fontSize: 11, lineHeight: 17, marginTop: 9 }, notificationAction: { alignSelf: 'flex-end', paddingTop: 8, paddingHorizontal: 4 }, notificationActionText: { color: ORANGE, fontSize: 10, fontWeight: '500' }, notificationEmpty: { color: '#777777', fontSize: 11, textAlign: 'center', paddingVertical: 10 },
  welcomePage: { flex: 1, padding: 24, alignItems: 'center', justifyContent: 'space-between' }, welcomeBrand: { flex: 1, alignItems: 'center', justifyContent: 'center' }, welcomeImage: { width: 240, height: 170 },
  tagline: { color: '#777777', fontSize: 12, letterSpacing: 0.4, textAlign: 'center', marginTop: 8 }, authPage: { padding: 20, paddingBottom: 32, alignItems: 'stretch' }, authLogo: { alignSelf: 'center', width: 170, height: 58, marginTop: 6 }, authSubtitle: { color: '#526174', fontSize: 14, textAlign: 'center', marginVertical: 22 }, authCard: { backgroundColor: WHITE, borderRadius: 20, padding: 20, elevation: 3 }, field: { marginBottom: 14, position: 'relative' }, fieldLabel: { color: NAVY, fontWeight: '600', fontSize: 12, marginBottom: 7 }, input: { backgroundColor: WHITE, minHeight: 46, borderWidth: 1, borderColor: '#D6DDE4', borderRadius: 10, paddingHorizontal: 13, paddingVertical: 10, color: NAVY }, showPassword: { position: 'absolute', right: 12, bottom: 13, color: ORANGE, fontSize: 12 },
  header: { minHeight: 54, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 14, backgroundColor: WHITE }, headerTitle: { color: NAVY, fontSize: 17, fontWeight: '600' }, back: { width: 42, alignItems: 'center' }, backText: { fontSize: 36, lineHeight: 40, color: NAVY }, homeHeader: { height: 54, backgroundColor: WHITE, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 16 }, brandText: { color: NAVY, fontSize: 21, fontWeight: '700' }, homeLogo: { width: 116, height: 42 }, headerAction: { width: 48, height: 48, alignItems: 'center', justifyContent: 'center' }, headerIcon: { color: ORANGE, fontSize: 24 }, avatarButton: { width: 44, height: 44, alignItems: 'center', justifyContent: 'center' }, avatar: { overflow: 'hidden', backgroundColor: NAVY, color: WHITE, fontSize: 14, fontWeight: '700', textAlign: 'center', textAlignVertical: 'center', width: 30, height: 30, borderRadius: 15 },
  page: { padding: 20, paddingBottom: 28 }, title: { color: NAVY, fontSize: 22, fontWeight: '700', textAlign: 'center', marginBottom: 8 }, muted: { color: '#666666', fontSize: 12, lineHeight: 18, marginBottom: 12 }, bodyText: { color: NAVY, fontSize: 14, lineHeight: 21 }, card: { backgroundColor: WHITE, borderRadius: 15, padding: 16, marginVertical: 7, elevation: 1 }, cardTitle: { color: NAVY, fontSize: 15, fontWeight: '700', marginBottom: 6 }, button: { minHeight: 48, backgroundColor: ORANGE, borderRadius: 12, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 16, marginVertical: 7, elevation: 2 }, buttonText: { color: WHITE, fontWeight: '700', fontSize: 15, textAlign: 'center' }, buttonSecondary: { backgroundColor: NAVY }, buttonSecondaryText: { color: WHITE }, disabled: { opacity: 0.55 }, link: { color: ORANGE, fontWeight: '600', fontSize: 12 }, linkCenter: { color: '#777777', fontSize: 13, textAlign: 'center', padding: 12, textDecorationLine: 'underline' },
  roleCard: { backgroundColor: WHITE, borderRadius: 12, borderWidth: 1, borderColor: '#E2E2E2', marginVertical: 9, padding: 12, alignItems: 'center' }, roleSelected: { borderColor: ORANGE, backgroundColor: '#FFF8F1' }, roleImage: { width: 142, height: 144 }, roleText: { color: WHITE, backgroundColor: NAVY, overflow: 'hidden', borderRadius: 7, textAlign: 'center', width: '100%', padding: 12, fontSize: 16 }, languageImage: { width: 200, height: 170, alignSelf: 'center', marginVertical: 6 }, languageChoice: { backgroundColor: WHITE, borderRadius: 9, padding: 14, marginVertical: 5, flexDirection: 'row', justifyContent: 'space-between', borderWidth: 1, borderColor: '#DDDDDD' }, languageSelected: { borderColor: ORANGE, backgroundColor: '#FFF8F1' }, languagePill: { borderWidth: 1, borderColor: '#D9DEE4', borderRadius: 18, paddingHorizontal: 12, paddingVertical: 8, margin: 4 },
  homePage: { paddingHorizontal: 18, paddingTop: 14, paddingBottom: 24 }, greeting: { flexDirection: 'row', alignItems: 'center', minHeight: 96 }, greetingCard: { padding: 16, minHeight: 108, marginBottom: 10 }, greetingTitle: { color: NAVY, fontSize: 18, fontWeight: '700', marginBottom: 8 }, flex: { flex: 1 }, greetingImage: { width: 112, height: 104 }, featureCard: { minHeight: 110, flexDirection: 'row', alignItems: 'center', backgroundColor: NAVY, borderRadius: 13, paddingHorizontal: 18, paddingVertical: 16, marginVertical: 6, elevation: 3 }, featureIconCircle: { width: 38, height: 38, borderRadius: 19, backgroundColor: WHITE, alignItems: 'center', justifyContent: 'center', marginRight: 12 }, featureCopy: { flex: 1 }, featureTitle: { color: WHITE, fontSize: 15, lineHeight: 19, fontWeight: '700', marginBottom: 5 }, featureSubtitle: { color: '#D7E0E6', fontSize: 11, lineHeight: 16 }, featureArrow: { color: WHITE, fontSize: 30, paddingLeft: 8 }, clipboardIcon: { width: 15, height: 18, backgroundColor: ORANGE, borderRadius: 2, alignItems: 'center', paddingTop: 3 }, clipboardClip: { position: 'absolute', top: -2, width: 8, height: 4, borderRadius: 2, borderWidth: 1, borderColor: ORANGE, backgroundColor: WHITE }, clipboardCheck: { color: WHITE, fontSize: 8, lineHeight: 8, fontWeight: '700' }, clipboardLine: { width: 8, height: 1, backgroundColor: WHITE, marginTop: 2 }, heartIcon: { color: ORANGE, fontSize: 23, lineHeight: 27 }, clockIcon: { width: 19, height: 19, borderRadius: 10, backgroundColor: ORANGE, position: 'relative' }, clockHandLong: { position: 'absolute', width: 2, height: 6, top: 4, left: 8, backgroundColor: WHITE, borderRadius: 1 }, clockHandShort: { position: 'absolute', width: 5, height: 2, top: 9, left: 9, backgroundColor: WHITE, borderRadius: 1 }, settingsIcon: { color: ORANGE, fontSize: 22, lineHeight: 26 }, statRow: { flexDirection: 'row', justifyContent: 'space-around', paddingTop: 10 }, stat: { color: NAVY, fontSize: 22, textAlign: 'center', lineHeight: 27 }, smallText: { color: '#666666', fontSize: 11 }, tipRow: { flexDirection: 'row', alignItems: 'flex-start' }, tipIconCircle: { width: 32, height: 32, borderRadius: 16, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', marginRight: 10 }, eyeTipGlyph: { width: 16, height: 20, alignItems: 'center', justifyContent: 'center' }, eyeTipBulb: { width: 13, height: 13, borderRadius: 7, backgroundColor: WHITE, alignItems: 'center', justifyContent: 'center' }, eyeTipHighlight: { width: 3, height: 3, borderRadius: 2, backgroundColor: ORANGE, position: 'absolute', top: 2, left: 3 }, eyeTipBulbNeck: { width: 7, height: 2, backgroundColor: WHITE, marginTop: -1 }, eyeTipBulbBase: { width: 6, height: 2, borderRadius: 1, backgroundColor: WHITE, marginTop: 1 }, activityRow: { flexDirection: 'row', alignItems: 'center', paddingTop: 10 }, activityIconCircle: { width: 26, height: 26, borderRadius: 13, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', marginRight: 8 }, activityIcon: { color: WHITE, fontSize: 15, fontWeight: '700' }, activityCopy: { flex: 1 }, activityTitle: { color: NAVY, fontSize: 12, marginBottom: 2 }, retakeButton: { backgroundColor: NAVY, borderRadius: 6, paddingVertical: 5, paddingHorizontal: 10 }, retakeText: { color: WHITE, fontSize: 10, fontWeight: '600' }, recommendation: { color: NAVY, paddingVertical: 6, fontSize: 14 }, resultEye: { color: ORANGE, fontSize: 56, textAlign: 'center' }, resultRisk: { fontSize: 35, fontWeight: '800', textAlign: 'center' },
  bottomNav: { height: 62, backgroundColor: WHITE, flexDirection: 'row', borderTopWidth: 1, borderTopColor: '#E8E6E4', justifyContent: 'space-around', alignItems: 'center' }, navItem: { flex: 1, alignItems: 'center' }, navIcon: { fontSize: 18, color: '#9A9A9A' }, navLabel: { fontSize: 9, color: '#9A9A9A', marginTop: 2 }, navSelected: { color: ORANGE, fontWeight: '700' }, switchRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 10 }, inlineRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, checkbox: { color: NAVY, fontSize: 18, marginRight: 7 }, separator: { color: '#7A8492', fontSize: 11, textAlign: 'center', marginVertical: 16 }, socialButton: { minHeight: 42, borderRadius: 10, borderWidth: 1, borderColor: '#E0E3E8', alignItems: 'center', justifyContent: 'center', marginTop: 9 }, bottomLink: { alignItems: 'center', marginTop: 18 }, tourImage: { width: '100%', height: 260, marginVertical: 10 }, wrapRow: { flexDirection: 'row', flexWrap: 'wrap', marginTop: 10 }, frequency: { flexGrow: 1, minWidth: '22%', borderRadius: 8, borderWidth: 1, borderColor: '#D5DBE1', paddingVertical: 10, paddingHorizontal: 5, marginRight: 4, marginBottom: 4, alignItems: 'center' }, frequencySelected: { backgroundColor: NAVY, borderColor: NAVY }, frequencyText: { color: NAVY, fontSize: 11 }, frequencyTextSelected: { color: WHITE, fontWeight: '700' },
});
