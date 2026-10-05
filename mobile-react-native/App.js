import { useEffect, useRef, useState } from 'react';
import { createContext, useContext } from 'react';
import Constants from 'expo-constants';
import {
  Alert,
  Animated,
  AppState,
  BackHandler,
  Easing,
  Image,
  KeyboardAvoidingView,
  Modal,
  Platform,
  Pressable,
  RefreshControl,
  ScrollView,
  StatusBar,
  StyleSheet,
  Switch,
  Text as NativeText,
  TextInput,
  View,
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { SafeAreaProvider, SafeAreaView } from 'react-native-safe-area-context';
import Ionicons from '@expo/vector-icons/Ionicons';
import Slider from '@react-native-community/slider';
import translatedStrings from './translations/strings.json';

const packagerHost = Constants.expoConfig?.hostUri?.split(':')[0];
const defaultApiHost = Platform.OS === 'android' ? '10.0.2.2' : packagerHost || '127.0.0.1';
const API_URL = process.env.EXPO_PUBLIC_API_URL || `http://${defaultApiHost}:8000/api`;
const ORANGE = '#F58216';
const NAVY = '#062A3C';
const WARM = '#F2EFED';
const WHITE = '#FFFFFF';
// Symptoms of the earlier five-question assessment, still shown in older saved results.
const SYMPTOMS = ['Eye pain', 'Dry eyes', 'Blurred vision', 'Headache', 'Eye fatigue'];
// Student questionnaire in the designed order. `symptom` is the key the API stores.
const STUDENT_QUESTIONS = [
  { symptom: 'Burning sensation', question: 'Do your eyes feel a burning sensation during or after using your gadgets for school?', image: require('./assets/images/assessment/q1.png') },
  { symptom: 'Itchy eyes', question: 'Do you experience itchy eyes after hours of reading or watching lectures on screen?', image: require('./assets/images/assessment/q2.png') },
  { symptom: 'Foreign body sensation', question: "Do you feel like there's something in your eye while studying or scrolling on your phone?", image: require('./assets/images/assessment/q3.png') },
  { symptom: 'Watery eyes', question: 'Do your eyes water while attending online classes or reading e-books?', image: require('./assets/images/assessment/q4.png') },
  { symptom: 'Excessive blinking', question: 'Do you blink excessively when focusing on a digital screen?', image: require('./assets/images/assessment/q5.png') },
  { symptom: 'Eye redness', question: 'Do your eyes turn red after long hours of studying online?', image: require('./assets/images/assessment/q6.png') },
  { symptom: 'Eye pain', question: 'Do you feel pain or discomfort in your eyes after using digital devices for school?', image: require('./assets/images/assessment/q7.png') },
  { symptom: 'Heavy eyelids', question: 'Do your eyelids feel heavy during or after screen time for academic work?', image: require('./assets/images/assessment/q8.png') },
  { symptom: 'Dry eyes', question: 'Do your eyes feel dry during long periods of online activities?', image: require('./assets/images/assessment/q9.png') },
  { symptom: 'Blurred vision', question: 'Do you experience blurry vision after staring at your screen for too long?', image: require('./assets/images/assessment/q10.png') },
  { symptom: 'Double vision', question: 'Do you sometimes see double images when reading or watching videos online?', image: require('./assets/images/assessment/q11.png') },
  { symptom: 'Difficulty focusing', question: 'Do you find it hard to focus on nearby text after using your device for schoolwork?', image: require('./assets/images/assessment/q12.png') },
  { symptom: 'Light sensitivity', question: 'Do your eyes feel more sensitive to light when using your devices?', image: require('./assets/images/assessment/q13.png') },
  { symptom: 'Colored halos', question: 'Do you see colored halos or glares around images on your screen?', image: require('./assets/images/assessment/q14.png') },
  { symptom: 'Worsening vision', question: 'Do you feel like your vision is slowly getting worse due to screen use?', image: require('./assets/images/assessment/q15.png') },
  { symptom: 'Worsening vision (Q16)', question: 'Do you feel like your vision is slowly getting worse due to screen use?', image: require('./assets/images/assessment/q16.png') },
];
// Professional questionnaire in the designed order, with the same illustrations as the student one.
const PROFESSIONAL_QUESTIONS = [
  ['Burning sensation', 'Do your eyes feel a burning sensation during or after working long hours in front of screen?'],
  ['Itchy eyes', 'Do you experience itchy eyes after using a computer for extended periods at work?'],
  ['Foreign body sensation', "Do you feel like there's something in your eye while you're working on digital tasks?"],
  ['Watery eyes', 'Do your eyes water while working on documents, emails, or spreadsheets?'],
  ['Excessive blinking', 'Do you notice yourself blinking more than usual during intense screen time?'],
  ['Eye redness', 'Do your eyes turn red by the end of your workday?'],
  ['Eye pain', 'Do you feel eye pain or discomfort after a full shift using your computer?'],
  ['Heavy eyelids', 'Do your eyelids feel heavy while finishing tasks or joining online meetings?'],
  ['Dry eyes', 'Do your eyes feel dry after hours of working in front of a digital screen?'],
  ['Blurred vision', 'Do you experience blurry vision after prolonged work-related screen use?'],
  ['Double vision', 'Do you sometimes see double images when switching between tabs or windows on your computer?'],
  ['Difficulty focusing', 'Do you struggle to focus on nearby objects or papers after working on your screen?'],
  ['Light sensitivity', 'Do you feel more sensitive to overhead lights or screen brightness during work?'],
  ['Colored halos', 'Do you see colored halos or reflections around screen text or objects after working too long?'],
  ['Worsening vision', 'Do you feel like your vision has been getting worse from regular work screen use?'],
  ['Headache', 'Do you experience headaches at work after being on your computer for hours?'],
].map(([symptom, question], index) => ({ symptom, question, image: STUDENT_QUESTIONS[index].image }));
// Listed from highest to lowest, as in the design; `value` is the score sent to the API.
const ANSWER_OPTIONS = [{ label: 'Often or Always', value: 2 }, { label: 'Occasionally', value: 1 }, { label: 'Never', value: 0 }];
const ANSWER_GUIDE = [
  ['Never', "You don't experience the symptom at all"],
  ['Occasionally', 'You feel it sometimes, maybe once a week'],
  ['Often or Always', 'You feel it 2–3 times a week or almost every day'],
];
// Result screens for the 16-question assessment, keyed by the API's risk level.
const ASSESSMENT_RESULTS = {
  LOW: {
    label: 'Mild Eye Strain', color: '#138A07', occasionallyColor: '#E46F0A', image: require('./assets/images/assessment/result-mild.png'),
    message: 'Good job! Your eyes are doing okay. Keep following healthy screen habits.',
    tipsTitle: 'Take Care of Your Eyes', tipsIntro: "You're doing well. Keep these healthy habits going!",
    tips: [
      ['eye', 'Blink more often during screen time', 'Remember to blink frequently to keep your eyes moist and reduce dryness while using digital devices.'],
      ['time', 'Follow the 30-30-30 rule', 'Every 30 minutes, look at something 30 feet away for at least 30 seconds to give your eyes a break.'],
      ['resize', "Keep your screen an arm's length away", 'Position your screen about 20-26 inches from your eyes to reduce strain and maintain proper posture.'],
      ['moon', 'Use dark mode during low- light study', 'Switch to dark mode when studying in dimly lit environments to reduce screen brightness and eye strain.'],
    ],
  },
  MEDIUM: {
    label: 'Moderate Eye Strain', color: '#E46F0A', occasionallyColor: '#E46F0A', image: require('./assets/images/assessment/result-moderate.png'),
    message: "Be careful! You're starting to feel the effects of DES. Try more frequent breaks and follow our tips.",
    tipsTitle: 'Support Your Eye Health', tipsIntro: 'You may be starting to feel screen strain. Try these to reduce discomfort.',
    tips: [
      ['time', 'Set Screen Break Reminders', 'Take a 20-second break every 30 minutes to rest your eyes and reduce strain.'],
      ['refresh', 'Try Eye Rolling Exercises', 'Perform gentle eye movements daily to strengthen eye muscles and improve circulation.'],
      ['sunny', 'Adjust screen brightness', 'Match your screen brightness to your room lighting to reduce eye strain.'],
    ],
  },
  HIGH: {
    label: 'Severe Eye Strain', color: '#E5121B', occasionallyColor: '#C9A400', image: require('./assets/images/assessment/result-severe.png'),
    message: 'Your eyes are under a lot of strain. Follow care tips immediately and consider reducing your screen time.',
    tipsTitle: 'Take Action Now!', tipsIntro: 'Your eyes are showing signs of high strain. Follow these steps right away.', urgent: true,
    tips: [
      ['desktop', 'Reduce Screen Time', 'Take frequent breaks and limit unnecessary screen exposure whenever possible.'],
      ['eye', 'Daily Eye Exercises', 'Practice blinking and focus shift exercises daily to strengthen your eye muscles.'],
      ['moon', 'Enable Dark Mode', 'Switch to dark mode and reduce screen contrast to minimize eye strain.'],
      ['time', '30-30-30 Rule', 'Every 30 minutes, look at something 30 feet away for at least 30 seconds.'],
      ['bulb', 'Low-Light Rest', 'Rest in dim lighting to reduce light sensitivity and give your eyes time to recover.'],
    ],
    note: 'If symptoms persist or worsen, consider consulting an eye care professional.',
  },
};
const SCREEN_TIME_GOAL_MINUTES = 180;
const WEEK_DAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
// Local calendar day as YYYY-MM-DD, so days roll over at local midnight.
const dayKey = (date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
// The seven days (Monday first) of the week containing `date`, shifted by `weeksBack`.
const weekDates = (date = new Date(), weeksBack = 0) => {
  const monday = new Date(date.getFullYear(), date.getMonth(), date.getDate() - ((date.getDay() + 6) % 7) - weeksBack * 7);
  return WEEK_DAYS.map((_, index) => new Date(monday.getFullYear(), monday.getMonth(), monday.getDate() + index));
};
const formatMinutes = (minutes) => (minutes < 60 ? `${minutes}m` : `${Math.floor(minutes / 60)}h ${minutes % 60}m`);
// Longest run of consecutive days for which `test` holds.
const longestRun = (dailyStats, test) => {
  const days = Object.keys(dailyStats).filter((key) => test(dailyStats[key])).sort();
  let best = 0;
  let run = 0;
  let previous = null;
  days.forEach((key) => {
    const date = new Date(`${key}T00:00:00`);
    run = previous && (date - previous) / 86400000 === 1 ? run + 1 : 1;
    best = Math.max(best, run);
    previous = date;
  });
  return best;
};
const SEVERITY = { LOW: ['Good', '#1DB815'], MEDIUM: ['Moderate', '#F58216'], HIGH: ['Severe', '#E5121B'] };
const MILESTONES = [
  { id: 'firstSteps', title: 'First Steps', icon: 'star', color: '#F2D60C', hint: 'Sign in to MataKo' },
  { id: 'firstAssessment', title: 'First Self-Assessment', icon: 'checkmark-circle', color: '#1DB815', hint: 'Complete a self-assessment' },
  { id: 'breakStreak', title: '3-Day Break Streak', icon: 'flame', color: '#E08A0B', hint: 'Take breaks 3 days in a row' },
  { id: 'monochrome', title: 'Monochrome Mode', icon: 'eye', color: NAVY, hint: 'Turn on Monochrome mode' },
  { id: 'weekWarrior', title: 'Week Warrior', icon: 'calendar', color: '#E5121B', hint: 'Use MataKo 7 days in a row' },
  { id: 'timeKeeper', title: 'Time Keeper', icon: 'time', color: '#0A12C8', hint: 'Stay under your 3h goal on 5 days' },
  { id: 'breakMaster', title: 'Break Master', icon: 'trophy', color: '#B8860B', hint: 'Take 50 breaks' },
  { id: 'champion', title: '30-Day Champion', icon: 'medal', color: '#7A5AF8', hint: 'Use MataKo on 30 days' },
];
const milestoneStatus = ({ dailyStats, assessments, monochrome }) => {
  const days = Object.values(dailyStats);
  const usedDays = days.filter((day) => day.minutes > 0);
  return {
    firstSteps: true,
    firstAssessment: (assessments || []).length > 0,
    breakStreak: longestRun(dailyStats, (day) => day.breaks > 0) >= 3,
    monochrome: Boolean(monochrome),
    weekWarrior: longestRun(dailyStats, (day) => day.minutes > 0) >= 7,
    timeKeeper: usedDays.filter((day) => day.minutes < SCREEN_TIME_GOAL_MINUTES).length >= 5,
    breakMaster: days.reduce((sum, day) => sum + (day.breaks || 0), 0) >= 50,
    champion: usedDays.length >= 30,
  };
};
// `impactTitle`/`symptomsTitle` default to the Eye Movement wording; `moreSymptoms` appear after View More.
const EYE_TOPICS = [
  {
    id: 'movement', title: 'Eye Movement & Tracking', icon: 'eye', summary: 'Understand how your eyes follow movement and how to improve tracking.',
    subtitle: 'Understanding Digital Eye Strain (DES) and its impact on visual tracking.',
    impact: 'Digital Eye Strain (DES) can impair smooth tracking and cause eye discomfort when reading long documents, scrolling, or switching windows.',
    symptoms: [['eye-off', 'Tracking Difficulties', 'Trouble following text across the screen'], ['reorder-four', 'Scrolling Discomfort', 'Eye strain during vertical movement'], ['albums', 'Window Switching', 'Fatigue when changing focus between screens']],
    tips: [['30-30-30 Rule', 'Every 30 minutes, look at something 30 feet away for 30 seconds.'], ['Eye Rolling', 'Roll your eyes slowly in circles to loosen the muscles that move them.'], ['Increase Text Size', 'Larger text is easier to follow and reduces how far your eyes jump across a line.']],
    prompt: ['Want to improve how your eyes follow movement?', "Let's explore daily habits and exercises to strengthen your eye tracking.", 'Not Now', 'Learn Now'],
  },
  {
    id: 'focus', title: 'Accommodation & Focus Fatigue', icon: 'locate', summary: 'Why your eyes get tired when switching focus—and how to fix it.',
    subtitle: 'Difficulty shifting focus between near and far objects, often caused by prolonged screen use.',
    impactTitle: 'Focus Difficulty',
    impact: 'Digital Eye Strain (DES) often leads to difficulty shifting focus between distances, a symptom of tired eye muscles.',
    symptomsTitle: 'Related Symptoms',
    symptoms: [['eye', 'Blurred Vision', 'Difficulty seeing clearly at different distances'], ['sad', 'Eye Fatigue', 'Tired, heavy feelings in the eyes'], ['medical', 'Headaches', 'Tension headaches from eye strain']],
    tips: [['30-30-30 Rule', 'Every 30 minutes, look at something 30 feet away for 30 seconds.'], ['Focus Shifting', 'Alternate between a near object and something 10–20 feet away.'], ['Screen Distance', "Keep your screen about an arm's length away from your eyes."]],
    prompt: ['Having trouble refocusing your eyes during screen use?', 'Learn why it happens and how to reduce focus fatigue.', 'Later', 'Continue'],
  },
  {
    id: 'light', title: 'Light Sensitivity', heroTitle: 'Light Sensitivity (Photophobia)', icon: 'sunny', summary: 'Understand what causes discomfort in bright light and how to manage it.',
    subtitle: 'A common symptom of Dry Eye Syndrome affecting daily activities.',
    impactTitle: 'What is Photophobia?',
    impact: 'Light sensitivity or photophobia is when your eyes become uncomfortable or painful when exposed to normal levels of light. This is particularly common in people with Dry Eye Syndrome.',
    symptoms: [['eye', 'Eye Discomfort', 'Pain or burning sensation'], ['sad', 'Bright Light Sensitivity', 'Difficulty with sunlight or bright indoor light'], ['medical', 'Screen Glare Issues', 'Difficulty using computers or phones']],
    moreSymptoms: [['eye-off', 'Blurred Vision', 'Trouble focusing on digital screens']],
    tips: [['Adjust Brightness', 'Match your screen brightness to the lighting in your room.'], ['Use Dark Mode', 'Switch to dark mode when you are in a dim environment.'], ['Reduce Glare', 'Position your screen away from windows and direct overhead lights.']],
    prompt: ['Sensitive to bright light or screens?', 'Discover common triggers and how to manage light sensitivity effectively.', 'Cancel', 'View Info'],
  },
];
// In the order shown on the Progress Tracker.
const EXERCISES = [
  { id: 'palming', name: 'Eye Palming', card: 'Eye Palming', icon: 'hand-left', description: 'Cover your eyes with your palms to relax them', page: 'Eye Palming Exercise', seconds: 60,
    why: 'Why Do Eye Palming Exercises?', whyText: 'Gently covering your eyes with your palms helps relax eye muscles and reduce tension from extended screen time.', tip: 'Rub your hands together to warm them up before palming — the warmth enhances relaxation.' },
  { id: 'blinking', name: 'Eye Blinking', card: 'Eye Blinking', icon: 'eye', description: 'Blink slowly to keep your eyes moist', page: 'Eye Blinking Exercise', seconds: 30,
    why: 'Why Blink Eye Exercises Matter?', whyText: 'Blinking helps spread moisture across your eyes and prevents dryness caused by long screen time.', tip: 'Try to blink every 4–6 seconds while using your device to keep your eyes refreshed.' },
  { id: 'focus', name: 'Focus Shifting', card: 'Focus Shift', icon: 'locate', description: 'Switch focus between near and far objects', page: 'Focus Shift Exercise', seconds: 30,
    why: 'Why Do Focus Shift Exercises?', whyText: 'Shifting focus between near and far objects helps your eyes relax and maintain flexibility, especially after long periods of screen use.', tip: 'Every 30 minutes, try looking at something 30 feet away for 30 seconds — it gives your eyes a well-deserved reset.' },
  { id: 'rule', name: '30-30-30 Rule', card: '30-30-30 Rule', icon: 'time', description: 'Look 30 feet away for 30 seconds', page: '30-30-30 Rule Exercise', seconds: 30,
    why: 'Why the 30-30-30 Rule Matters?', whyText: 'Looking 30 feet away for 30 seconds every 30 minutes helps reduce eye strain and gives your focusing muscles a chance to relax.', tip: 'Set a reminder so you remember to look away while you work.' },
  { id: 'rolling', name: 'Eye Rolling', card: 'Eye Rolling', icon: 'refresh', description: 'Roll your eyes in slow circles to ease tension', page: 'Eye Rolling Relaxation', seconds: 30,
    why: 'Why Eye Rolling Exercises Matter?', whyText: 'Rolling your eyes gently helps relax strained eye muscles, improve flexibility, and reduce tension built up from focusing on a screen.', tip: 'Move slowly and keep your head still — only your eyes should move.' },
];
const formatClock = (seconds) => `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;
// Countdown for an exercise: idle → running ⇄ paused → done.
function useCountdown(total, onDone) {
  const [remaining, setRemaining] = useState(total);
  const [status, setStatus] = useState('idle');
  useEffect(() => { if (status === 'idle') setRemaining(total); }, [total, status]);
  useEffect(() => {
    if (status !== 'running') return undefined;
    const timer = setInterval(() => setRemaining((value) => Math.max(0, value - 1)), 1000);
    return () => clearInterval(timer);
  }, [status]);
  useEffect(() => {
    if (status === 'running' && remaining === 0) { setStatus('done'); onDone?.(); }
  }, [remaining, status]);
  return {
    remaining, status,
    start: () => { setRemaining(total); setStatus('running'); },
    pause: () => setStatus('paused'),
    resume: () => setStatus('running'),
    stop: () => { setStatus('idle'); setRemaining(total); },
  };
}
// Student Tips page. Items are [icon, icon colour, title, text].
const STUDENT_TIPS = [
  { id: 'daily', icon: 'eye', title: 'Daily Eye Care', text: 'Simple habits to keep your eyes healthy during class, study, and screen time.', items: [
    ['water', '#2E90FA', 'Stay Hydrated', 'Drink 8 glasses of water daily to keep your eyes naturally lubricated.'],
    ['sunny', '#F5C518', 'Wear Sunglasses', 'Protect your eyes from UV rays when outdoors, even on cloudy days.'],
    ['moon', '#7A5AF8', 'Get Quality Sleep', 'Aim for 7–8 hours of sleep to allow your eyes to rest and recover.'],
  ] },
  { id: 'screen', icon: 'desktop', title: 'Screen Use', text: 'Manage screen brightness, reduce glare, and use blue light filters while studying.', items: [
    ['time', '#16A34A', '30-30-30 Rule', 'Every 30 minutes, look at something 30 feet away for 30 seconds.'],
    ['sunny', '#98A2B3', 'Adjust Brightness', 'Match your screen brightness to your study area so it is not brighter than the room.'],
    ['glasses', '#2E90FA', 'Use a Blue Light Filter', 'Turn on night mode or a blue light filter during evening study sessions.'],
  ] },
  { id: 'study', icon: 'book', title: 'Study Habits', text: 'Prevent eye strain with proper posture, printed notes, and timed visual breaks.', items: [
    ['body', '#2E90FA', 'Keep Good Posture', 'Sit upright with your screen slightly below eye level and about an arm’s length away.'],
    ['document-text', '#F5C518', 'Use Printed Notes', 'Review printed notes or books when you can to give your eyes a break from screens.'],
    ['alarm', '#E5121B', 'Take Timed Breaks', 'Rest your eyes for a few minutes after every study session or online class.'],
  ] },
  { id: 'lifestyle', icon: 'heart', title: 'Lifestyle & Wellness', text: 'Balance screen-heavy routines with good sleep, eye-friendly foods, and physical activity.', items: [
    ['nutrition', '#16A34A', 'Eat Eye-Healthy Foods', 'Include carrots, leafy greens, and fish rich in omega-3 in your diet.'],
    ['barbell', '#7A5AF8', 'Eye Exercises', 'Practice simple eye movements and focus exercises daily.'],
    ['person', '#2E90FA', 'Regular Check-ups', 'Visit an eye doctor annually for comprehensive eye examinations.'],
  ] },
];
const DEFAULT_DAILY_TIP = 'Remember to blink more often while using your screen. The 30-30-30 rule can help reduce eye strain.';
const DEFAULT_FACTS = [['alert-circle', 'Staring at a screen reduces your blink rate by up to 60%!'], ['moon', 'Blue light exposure before sleep can delay melatonin release']];
// Professional Tips page, same item format.
const PROFESSIONAL_TIPS = [
  { id: 'daily', icon: 'eye', title: 'Daily Eye Care', text: 'Easy techniques to reduce eye fatigue throughout your workday.', items: [
    ['water', '#2E90FA', 'Stay Hydrated', 'Drink 8 glasses of water daily to keep your eyes naturally lubricated.'],
    ['sunny', '#F5C518', 'Wear Sunglasses', 'Protect your eyes from UV rays when outdoors, even on cloudy days.'],
    ['moon', '#7A5AF8', 'Get Quality Sleep', 'Aim for 7–8 hours of sleep to allow your eyes to rest and recover.'],
  ] },
  { id: 'screen', icon: 'desktop', title: 'Screen Use', text: 'Adjust screen settings and lighting for long office or remote work sessions.', items: [
    ['time', '#16A34A', '30-30-30 Rule', 'Every 30 minutes, look at something 30 feet away for 30 seconds.'],
    ['sunny', '#98A2B3', 'Adjust Brightness', 'Match your screen brightness to your surrounding environment.'],
    ['resize', '#2E90FA', 'Proper Distance', 'Keep screens 20-26 inches away and slightly below eye level.'],
  ] },
  { id: 'work', icon: 'briefcase', title: 'Work Habits', text: 'Stay productive without sacrificing your eyes—use scheduled breaks and ergonomic setups.', items: [
    ['bulb', '#F5C518', 'Good Lighting', 'Use adequate lighting when reading to reduce eye strain and fatigue.'],
    ['pause', '#E5121B', 'Take Regular Breaks', 'Step away from your work every hour for a few minutes of rest.'],
  ] },
  { id: 'lifestyle', icon: 'heart', title: 'Lifestyle & Wellness', text: 'Balance screen-heavy routines with good sleep, eye-friendly foods, and physical activity.', items: [
    ['nutrition', '#16A34A', 'Eat Eye-Healthy Foods', 'Include carrots, leafy greens, and fish rich in omega-3 in your diet.'],
    ['barbell', '#7A5AF8', 'Eye Exercises', 'Practice simple eye movements and focus exercises daily.'],
    ['person', '#2E90FA', 'Regular Check-ups', 'Visit an eye doctor annually for comprehensive eye examinations.'],
  ] },
];
// Assessments record their own maximum; older ones are inferred from how many answers they have.
const maxScore = (assessment) => assessment?.max_score || (assessment?.symptoms?.length === SYMPTOMS.length ? SYMPTOMS.length * 3 : (assessment?.symptoms?.length || STUDENT_QUESTIONS.length) * 2);
// The original five-symptom assessment, scored out of 15, keeps its old result screen.
const isLegacyAssessment = (assessment) => maxScore(assessment) === SYMPTOMS.length * 3;
const BUNDLED_QUESTION_IMAGES = Object.fromEntries(STUDENT_QUESTIONS.map((item, index) => [`q${index + 1}`, item.image]));
const API_ORIGIN = API_URL.replace(/\/api\/?$/, '');
// Questions from the server; images are either built-in mascots or files uploaded in the admin.
const fromServerQuestions = (questions) => questions.map((item) => ({
  symptom: item.symptom,
  question: item.question,
  image: item.image_path ? { uri: `${API_ORIGIN}${item.image_path}` } : BUNDLED_QUESTION_IMAGES[item.image_key] || require('./assets/images/student.png'),
}));
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
    'Age': 'Edad', 'Confirm Password': 'Kumpirmahin ang password', 'Home': 'Tahanan',
    'Progress': 'Progreso', 'Eye Care': 'Pangangalaga sa mata', 'Tips': 'Mga payo', 'Settings': 'Mga Setting',
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
// Interface and built-in content strings: { English: [Filipino, Cebuano] }.
Object.entries(translatedStrings).forEach(([english, [filipino, cebuano]]) => {
  translations.Filipino[english] ??= filipino;
  translations.Cebuano[english] ??= cebuano;
});

// Icons follow the display theme like text does.
function Icon({ color, ...props }) {
  return <Ionicons color={paint(color)} {...props} />;
}

function Text({ children, style, ...props }) {
  const language = useContext(LanguageContext);
  const translateChild = (child) => {
    if (typeof child === 'string') return translations[language]?.[child] || child;
    if (Array.isArray(child)) return child.map(translateChild);
    return child;
  };
  // Shared styles are already themed; only inline colours need converting here.
  const themeInline = (item) => (item && typeof item === 'object' && !Array.isArray(item) && item.color && !themedStyles.has(item) ? { ...item, color: paint(item.color) } : item);
  const themedStyle = Array.isArray(style) ? style.map(themeInline) : themeInline(style);
  return <NativeText style={themedStyle} {...props}>{translateChild(children)}</NativeText>;
}

// The language chosen in the app; set while App renders so helpers outside components can use it.
let currentLanguage = 'English';
const LANGUAGE_CODES = { English: 'en', Filipino: 'fil', Cebuano: 'ceb' };
const translate = (value) => translations[currentLanguage]?.[value] || value;
const dateLocale = () => ({ Filipino: 'fil-PH', Cebuano: 'ceb-PH' })[currentLanguage] || 'en-US';
const formatDate = (value, options) => {
  try { return new Date(value).toLocaleDateString(dateLocale(), options); } catch { return new Date(value).toLocaleDateString('en-US', options); }
};
// Adds translations sent by the server (for content admins edit) to the app's dictionary.
const mergeTranslations = (incoming) => {
  ['Filipino', 'Cebuano'].forEach((language) => Object.assign(translations[language], incoming?.[language] || {}));
};

async function request(path, { token, method = 'GET', body } = {}) {
  let response;
  try {
    response = await fetch(`${API_URL}${path}`, {
      method,
      headers: {
        Accept: 'application/json',
        'Accept-Language': LANGUAGE_CODES[currentLanguage] || 'en',
        'Content-Type': 'application/json',
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
      ...(body ? { body: JSON.stringify(body) } : {}),
    });
  } catch {
    throw new Error(`${translate('Cannot connect to the MataKo server. Check your internet connection and try again.')}\n\n${API_URL}`);
  }
  const data = await response.json().catch(() => ({}));
  if (!response.ok) {
    const firstErrors = data.errors ? Object.values(data.errors).flat() : [];
    const error = new Error(firstErrors[0] || data.message || translate('Request failed. Please try again.'));
    error.status = response.status;
    throw error;
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
  // Loaded from the server so admins can change them; null means use the copies built into the app.
  const [serverQuestions, setServerQuestions] = useState(null);
  const [serverContent, setServerContent] = useState(null);
  const [legalTab, setLegalTab] = useState('Terms of Service');
  const [legalOrigin, setLegalOrigin] = useState('register');
  const [legalAccepted, setLegalAccepted] = useState(false);
  const [registerValues, setRegisterValues] = useState({ name: '', email: '', phone: '', age: '', password: '', password_confirmation: '' });
  const [remindersOn, setRemindersOn] = useState(false);
  const [monochrome, setMonochrome] = useState(false);
  // Display theme from Eye Care Settings; textContrast is a percentage where 85 is the design's default.
  const [display, setDisplay] = useState({ dark: false, highContrast: false, textContrast: 85 });
  applyTheme({ ...display, monochrome });
  currentLanguage = language;
  const updateDisplay = (changes) => setDisplay((current) => {
    const next = { ...current, ...changes };
    AsyncStorage.setItem('matako_display', JSON.stringify(next)).catch(() => {});
    return next;
  });
  // Minutes in MataKo and break reminders per day, keyed by dayKey(); kept on the device.
  const [dailyStats, setDailyStats] = useState({});
  const [dailyStatsLoaded, setDailyStatsLoaded] = useState(false);
  const [milestoneDates, setMilestoneDates] = useState({});
  // Eye exercises completed per day: { [dayKey]: [exercise ids] }.
  const [exerciseLog, setExerciseLog] = useState({});
  const completeExercise = (id) => setExerciseLog((current) => {
    const key = dayKey(new Date());
    const done = current[key] || [];
    if (done.includes(id)) return current;
    const next = { ...current, [key]: [...done, id] };
    AsyncStorage.setItem('matako_exercises', JSON.stringify(next)).catch(() => {});
    return next;
  });
  const remindersToday = dailyStats[dayKey(new Date())]?.breaks || 0;
  const appMinutes = dailyStats[dayKey(new Date())]?.minutes || 0;
  const bumpToday = (field) => setDailyStats((current) => {
    const key = dayKey(new Date());
    const day = current[key] || { minutes: 0, breaks: 0 };
    return { ...current, [key]: { ...day, [field]: (day[field] || 0) + 1 } };
  });
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
        const savedDisplay = await AsyncStorage.getItem('matako_display');
        if (savedDisplay) setDisplay((current) => ({ ...current, ...JSON.parse(savedDisplay) }));
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
      bumpToday('breaks');
      Alert.alert(t('Eye break'), t('Look 20 feet away for 20 seconds.'));
    }, 20 * 60 * 1000);
    return () => clearInterval(timer);
  }, [remindersOn]);

  useEffect(() => {
    if (!token) return undefined;
    (async () => {
      try {
        const [stored, legacy, milestones, exercises] = await Promise.all([
          AsyncStorage.getItem('matako_daily_stats'),
          AsyncStorage.getItem('matako_screen_time'),
          AsyncStorage.getItem('matako_milestones'),
          AsyncStorage.getItem('matako_exercises'),
        ]);
        if (exercises) setExerciseLog(JSON.parse(exercises));
        const stats = stored ? JSON.parse(stored) : {};
        // Carry over today's minutes saved by the earlier single-day format.
        if (!stored && legacy) {
          const usage = JSON.parse(legacy);
          stats[dayKey(new Date(usage.day))] = { minutes: usage.minutes || 0, breaks: 0 };
        }
        setDailyStats((current) => ({ ...stats, ...current }));
        if (milestones) setMilestoneDates(JSON.parse(milestones));
      } catch {}
      setDailyStatsLoaded(true);
    })();
    const timer = setInterval(() => {
      if (AppState.currentState === 'active') bumpToday('minutes');
    }, 60 * 1000);
    return () => clearInterval(timer);
  }, [token]);

  useEffect(() => {
    if (dailyStatsLoaded) AsyncStorage.setItem('matako_daily_stats', JSON.stringify(dailyStats)).catch(() => {});
  }, [dailyStats, dailyStatsLoaded]);

  // Record the first day each milestone is reached.
  useEffect(() => {
    if (!dailyStatsLoaded) return;
    const reached = milestoneStatus({ dailyStats, assessments, monochrome });
    const today = dayKey(new Date());
    const added = Object.fromEntries(Object.keys(reached).filter((id) => reached[id] && !milestoneDates[id]).map((id) => [id, today]));
    if (!Object.keys(added).length) return;
    const next = { ...milestoneDates, ...added };
    setMilestoneDates(next);
    AsyncStorage.setItem('matako_milestones', JSON.stringify(next)).catch(() => {});
  }, [dailyStats, assessments, monochrome, dailyStatsLoaded]);

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
        if (error.status === 401) {
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
      Alert.alert(t('Edit Profile'), t(error.message));
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
      Alert.alert(t('Sign In'), t(error.message));
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
      Alert.alert(t('Create Account'), t(error.message));
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

  useEffect(() => {
    if (!token) return undefined;
    let active = true;
    request('/content', { token }).then((data) => {
      if (!active) return;
      mergeTranslations(data.translations);
      setServerContent(data);
    }).catch(() => {});
    return () => { active = false; };
  }, [token, user?.role]);

  const startAssessment = async () => {
    setServerQuestions(null);
    setScreen('assessment');
    try {
      const data = await request('/questions', { token });
      mergeTranslations(data.translations);
      if (data.questions?.length) setServerQuestions(fromServerQuestions(data.questions));
    } catch {}
  };

  const submitAssessment = async (answers) => {
    setLoading(true);
    try {
      const data = await request('/assessment', { token, method: 'POST', body: { answers } });
      setResult(data);
      setScreen('result');
    } catch (error) {
      Alert.alert(t('Self-assessment'), t(error.message));
    } finally { setLoading(false); }
  };

  const refreshHistory = async () => {
    try {
      const data = await request('/assessments', { token });
      setAssessments(data.assessments || []);
    } catch (error) { Alert.alert('MataKo', t(error.message)); }
  };

  const openHistoryResult = async (id) => {
    try {
      const data = await request(`/assessment/${id}`, { token });
      setResult(data);
      setScreen('result');
    } catch (error) { Alert.alert('MataKo', t(error.message)); }
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

  const card = (children, style, key) => <View key={key} style={[styles.card, style]}>{children}</View>;

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
    display={display} onDisplay={updateDisplay} serverContent={serverContent}
    dailyStats={dailyStats} milestoneDates={milestoneDates} exerciseLog={exerciseLog} onCompleteExercise={completeExercise}
    selectedTab={homeTab} onTab={setHomeTab} onAssessment={startAssessment}
    onNotification={() => setScreen('notifications')} onHistory={openHistoryResult}
    onReminder={setRemindersOn} onMonochrome={(enabled) => { setMonochrome(enabled); AsyncStorage.setItem('matako_monochrome', String(enabled)).catch(() => {}); }} onLogout={logOut} onLanguage={saveLanguage} onSaveProfile={updateProfile} loading={loading}
    language={language} onLegal={(tab) => { setLegalTab(tab); setLegalOrigin('home'); setScreen('legal'); }}
    onRefresh={refreshHistory} t={t} card={card} button={button}
  />;

  const renderAssessment = () => {
    const student = user?.role !== 'professional';
    return <SelfAssessment questions={serverQuestions || (student ? STUDENT_QUESTIONS : PROFESSIONAL_QUESTIONS)} introImage={student ? require('./assets/images/student.png') : require('./assets/images/assessment/intro-professional.png')} name={user?.name?.split(' ')[0]} onBack={() => setScreen('home')} onSubmit={submitAssessment} loading={loading} t={t} header={header} />;
  };

  const renderResult = () => {
    const assessment = result?.assessment || {};
    if (!isLegacyAssessment(assessment)) {
      return <AssessmentResult assessment={assessment} results={serverContent?.results} t={t} header={header} onRetake={startAssessment} onHome={async () => { await refreshHistory(); setScreen('home'); }} />;
    }
    const risk = assessment.risk_level || 'LOW';
    const riskColor = risk === 'HIGH' ? '#B54708' : risk === 'MEDIUM' ? ORANGE : NAVY;
    return <>
      {header(t('Your result'), () => setScreen('home'))}
      <ScrollView contentContainerStyle={styles.page}>
        <Text style={styles.resultEye}>◉</Text>
        <Text style={[styles.resultRisk, { color: riskColor }]}>{t(risk)}</Text>
        <Text style={styles.bodyText}>{t('Digital eye strain risk · ')}{assessment.total_score || 0}{t(' of 15 points').replace('15', maxScore(assessment))}</Text>
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
      message: `${t('Your result is ready:')} ${t(item.risk_level || 'LOW')} ${t('risk')} (${item.total_score ?? 0} ${t('of 15').replace('15', maxScore(item))}).`,
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

  return <SafeAreaProvider><SafeAreaView style={[styles.safe, monochrome && styles.grayscale]}><LanguageContext.Provider value={language}><StatusBar barStyle={display.dark ? 'light-content' : 'dark-content'} backgroundColor={paint(WARM, 'background')} /><KeyboardAvoidingView style={styles.fill} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>{content}</KeyboardAvoidingView></LanguageContext.Provider></SafeAreaView></SafeAreaProvider>;
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
      <Image source={logoSource()} style={styles.authLogo} resizeMode="contain" />
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
  if (kind === 'tips') return <Icon name="heart" size={22} color={ORANGE} />;
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

function Home({ user, assessments, remindersOn, remindersToday, appMinutes, monochrome, display, onDisplay, serverContent, dailyStats, milestoneDates, exerciseLog, onCompleteExercise, selectedTab, onTab, onAssessment, onNotification, onHistory, onReminder, onMonochrome, onLogout, onLanguage, language, onLegal, onRefresh, onSaveProfile, loading, t, card, button }) {
  const [editingProfile, setEditingProfile] = useState(false);
  const [featurePrompt, setFeaturePrompt] = useState(null);
  const [progressView, setProgressView] = useState('overview');
  const [eyeCareStack, setEyeCareStack] = useState([]);
  const popEyeCare = () => setEyeCareStack((current) => current.slice(0, -1));
  const eyeCareBack = useRef(null);
  const progressDetail = (selectedTab === 'progress' && progressView !== 'overview') || (selectedTab === 'eyeCare' && eyeCareStack.length > 0);

  // Android back from an inner page returns to the page before it.
  useEffect(() => {
    if (!progressDetail) return undefined;
    const subscription = BackHandler.addEventListener('hardwareBackPress', () => {
      if (selectedTab === 'eyeCare') (eyeCareBack.current || popEyeCare)(); else setProgressView('overview');
      return true;
    });
    return () => subscription.remove();
  }, [progressDetail, selectedTab]);
  const [profileDraft, setProfileDraft] = useState({ name: '', email: '', phone: '', age: '', role: 'student' });
  const chooseTab = (name) => { setProgressView('overview'); setEyeCareStack([]); onTab(name); };
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
      {card(<View style={styles.tipRow}><View style={styles.tipIconCircle}><EyeTipGlyph /></View><View style={styles.flex}><Text style={styles.cardTitle}>{t("Today's Eye Tip")}</Text><Text style={styles.bodyText}>{t(serverContent?.daily_tip || DEFAULT_DAILY_TIP)}</Text></View></View>)}
      {card(<><Text style={styles.cardTitle}>{t('Recent Activity')}</Text>{latest ? <View style={styles.activityRow}>
        <View style={styles.activityIconCircle}><Text style={styles.activityIcon}>✓</Text></View>
        <View style={styles.activityCopy}><Text style={styles.activityTitle}>{t('Self-Assessment Completed')}</Text><Text style={styles.smallText}>{timeAgo(latest.created_at)}</Text></View>
        <Pressable accessibilityRole="button" onPress={confirmAssessmentStart} style={styles.retakeButton}><Text style={styles.retakeText}>{t('Retake')}</Text></Pressable>
      </View> : <Text style={styles.bodyText}>{t('No recent activity yet. Start a self-assessment to see your results here.')}</Text>}
      {remindersToday > 0 ? <View style={styles.activityRow}><View style={styles.activityIconCircle}><ProfileStatGlyph kind="clock" /></View><View style={styles.activityCopy}><Text style={styles.activityTitle}>{t('Break Reminder')}</Text><Text style={styles.smallText}>{remindersToday} {t('today')}</Text></View></View> : null}</>)}
    </ScrollView>
  ) : selectedTab === 'progress' ? (
    <Progress view={progressView} onView={setProgressView} dailyStats={dailyStats} assessments={rows} milestoneDates={milestoneDates} monochrome={monochrome} onHistory={onHistory} t={t} />
  ) : selectedTab === 'eyeCare' ? (
    <EyeCare topics={serverContent?.topics?.length ? serverContent.topics : EYE_TOPICS} exercises={serverContent?.exercises?.length ? serverContent.exercises : EXERCISES} stack={eyeCareStack} onPush={(page) => setEyeCareStack((current) => [...current, page])} onPop={popEyeCare} backRef={eyeCareBack} onAssessment={onAssessment} exerciseLog={exerciseLog} onCompleteExercise={onCompleteExercise} remindersOn={remindersOn} onReminder={onReminder} onSettings={() => chooseTab('settings')} t={t} />
  ) : selectedTab === 'tips' ? (
    <TipsPage categories={serverContent?.categories?.length ? serverContent.categories : user?.role === 'professional' ? PROFESSIONAL_TIPS : STUDENT_TIPS} dailyTip={serverContent?.daily_tip || DEFAULT_DAILY_TIP} facts={serverContent?.facts || DEFAULT_FACTS} t={t} />
  ) : selectedTab === 'settings' ? (
    <DisplaySettings display={display} onDisplay={onDisplay} monochrome={monochrome} onMonochrome={onMonochrome} t={t} />
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
        <View style={styles.profileSectionHeader}><Text style={styles.profileSectionTitle}>{t('Eye Care Settings')}</Text><Pressable accessibilityRole="button" onPress={() => chooseTab('settings')}><Text style={styles.profileMoreLink}>{t('More eye care settings')}  ›</Text></Pressable></View>
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
    {progressDetail ? null : selectedTab === 'profile' ? <View style={styles.profileHeader}>
      <Pressable accessibilityRole="button" accessibilityLabel="Back to Home" onPress={() => chooseTab('home')} style={styles.profileHeaderAction}><Text style={styles.profileHeaderBack}>‹</Text></Pressable>
      <Text style={styles.profileHeaderTitle}>{t('Profile')}</Text>
      <Pressable accessibilityRole="button" accessibilityLabel={t('Profile options')} onPress={() => Alert.alert(t('Profile options'), t('Choose an account action.'), [{ text: t('Log out'), style: 'destructive', onPress: onLogout }, { text: t('Cancel'), style: 'cancel' }])} style={styles.profileHeaderAction}><Text style={styles.profileMenu}>⋮</Text></Pressable>
    </View> : <View style={styles.homeHeader}>
      <Image accessibilityLabel="MataKo" source={logoSource()} style={styles.homeLogo} resizeMode="contain" />
      <View style={styles.inlineRow}>
        <Pressable accessibilityRole="button" accessibilityLabel="Notifications" hitSlop={8} onPress={onNotification} style={styles.headerAction}>
          <Text style={styles.headerIcon}>🔔</Text>
        </Pressable>
        <Pressable accessibilityRole="button" accessibilityLabel="Profile" hitSlop={8} onPress={() => chooseTab('profile')} style={styles.avatarButton}>
          <Text style={styles.avatar}>{user?.name?.[0]?.toUpperCase() || 'U'}</Text>
        </Pressable>
      </View>
    </View>}
    {content}
    {progressDetail ? null : <View style={styles.bottomNav}>{[['home', 'home', 'Home'], ['progress', 'trending-up', 'Progress'], ['eyeCare', 'eye', 'Eye Care'], ['tips', 'heart', 'Tips'], ['settings', 'settings-sharp', 'Settings']].map(([key, icon, label]) => <Pressable key={key} accessibilityRole="tab" accessibilityState={{ selected: selectedTab === key }} style={styles.navItem} onPress={() => chooseTab(key)}><Icon name={icon} size={22} color={selectedTab === key ? ORANGE : '#9A9A9A'} /><Text numberOfLines={1} adjustsFontSizeToFit style={[styles.navLabel, selectedTab === key && styles.navSelected]}>{t(`${label} (tab)`) === `${label} (tab)` ? t(label) : t(`${label} (tab)`)}</Text></Pressable>)}</View>}
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

// Eye Care tab: health topics, the exercises list and progress tracker, and one page per exercise.
// `stack` holds the pages opened on top of the overview, so Back returns to the previous one.
function EyeCare({ topics, exercises, stack, onPush, onPop, backRef, exerciseLog, onCompleteExercise, remindersOn, onReminder, onSettings, onAssessment, t }) {
  // { title, message, cancel, confirm, onConfirm }; `message` may be a React node.
  const [dialog, setDialog] = useState(null);
  const [moreSymptoms, setMoreSymptoms] = useState(false);
  // While an exercise is running, Back asks before leaving it.
  const [sessionActive, setSessionActive] = useState(false);
  const [leaveRequest, setLeaveRequest] = useState(0);
  const goBack = () => (sessionActive ? setLeaveRequest((count) => count + 1) : onPop());
  useEffect(() => { if (backRef) backRef.current = goBack; });
  useEffect(() => { setMoreSymptoms(false); setSessionActive(false); }, [stack.length]);
  const [showHistory, setShowHistory] = useState(false);
  const page = stack[stack.length - 1];
  // Leave a topic or exercise page if an admin has since removed what it shows.
  const missing = (page?.view === 'topic' && !topics.some((item) => item.id === page.id)) || (page?.view === 'exercise' && !exercises.some((item) => item.id === page.id));
  useEffect(() => { if (missing) onPop(); }, [missing]);
  const today = new Date();
  const doneToday = exerciseLog[dayKey(today)] || [];
  const exercisesThisWeek = weekDates(today).reduce((sum, date) => sum + (exerciseLog[dayKey(date)] || []).length, 0);
  let streak = 0;
  const streakDay = new Date(today);
  if (!doneToday.length) streakDay.setDate(streakDay.getDate() - 1);
  while ((exerciseLog[dayKey(streakDay)] || []).length) { streak += 1; streakDay.setDate(streakDay.getDate() - 1); }
  const longestStreak = Math.max(streak, longestRun(exerciseLog, (day) => day.length > 0));
  const percent = Math.round((doneToday.length / exercises.length) * 100);

  const exercisesPrompt = () => setDialog({
    title: 'Ready to continue your eye care exercises?',
    message: <>{t("You've completed")} <Text style={styles.bold}>{doneToday.length}</Text> {t('out of')} <Text style={styles.bold}>{exercises.length}</Text> {t("today. Let's keep going to give your eyes the care they deserve.")}</>,
    cancel: 'Not Now', confirm: 'Continue', onConfirm: () => onPush({ view: 'exercises' }),
  });
  const confirmDialog = <Modal visible={dialog !== null} transparent animationType="fade" statusBarTranslucent onRequestClose={() => setDialog(null)}>
    <View style={styles.assessmentPromptBackdrop}><View style={styles.assessmentPrompt}>
      <Text style={styles.assessmentPromptTitle}>{t(dialog?.title || '')}</Text>
      <Text style={styles.assessmentPromptMessage}>{typeof dialog?.message === 'string' ? t(dialog.message) : dialog?.message}</Text>
      <View style={styles.assessmentPromptActions}>
        <Pressable accessibilityRole="button" onPress={() => setDialog(null)} style={styles.assessmentPromptCancel}><Text style={styles.assessmentPromptCancelText}>{t(dialog?.cancel || 'Cancel')}</Text></Pressable>
        <Pressable accessibilityRole="button" onPress={() => { const next = dialog.onConfirm; setDialog(null); next(); }} style={styles.assessmentPromptNext}><Text style={styles.assessmentPromptNextText}>{t(dialog?.confirm || 'Open')}</Text></Pressable>
      </View>
    </View></View>
  </Modal>;
  return <>{renderPage()}{confirmDialog}</>;

  function renderPage() {
  const pageHeader = (title) => <View style={styles.progressHeader}>
    <Pressable accessibilityRole="button" accessibilityLabel={t('Back')} hitSlop={10} onPress={goBack} style={styles.progressBack}><Icon name="arrow-back" size={22} color={NAVY} /></Pressable>
    <Text style={styles.progressHeaderTitle} numberOfLines={1}>{t(title)}</Text>
    <View style={styles.progressBack} />
  </View>;
  const whyCard = (title, text, tip) => <View style={styles.whyCard}>
    <View style={styles.whyTitleRow}><Icon name="bulb" size={18} color={ORANGE} /><Text style={styles.whyTitle}>{t(title)}</Text></View>
    <Text style={styles.whyText}>{t(text)}</Text>
    {tip ? <View style={styles.tipCallout}><Icon name="information-circle" size={14} color={ORANGE} /><Text style={styles.tipCalloutText}>{t(tip)}</Text></View> : null}
  </View>;

  if (page?.view === 'topic') {
    const topic = topics.find((item) => item.id === page.id);
    if (!topic) return null;
    return <>
      {pageHeader(topic.title)}
      <ScrollView style={styles.progressPage} contentContainerStyle={styles.progressDetail}>
        <View style={styles.topicHero}>
          <Icon name={topic.icon} size={64} color={ORANGE} />
          <Text style={styles.topicTitle}>{t(topic.heroTitle || topic.title)}</Text>
          <Text style={styles.topicSubtitle}>{t(topic.subtitle)}</Text>
          <View style={styles.topicDivider} />
          <View style={styles.topicImpactRow}><View style={styles.topicCheck}><Icon name="checkmark" size={16} color={WHITE} /></View><Text style={styles.topicImpactTitle}>{t(topic.impactTitle || 'Digital Eye Strain (DES) Impact')}</Text></View>
          <Text style={styles.topicImpactText}>{t(topic.impact)}</Text>
        </View>
        <Text style={styles.topicSection}>{t(topic.symptomsTitle || 'Common Symptoms')}</Text>
        {[...topic.symptoms, ...(moreSymptoms ? topic.moreSymptoms || [] : [])].map(([icon, title, text]) => <View key={title} style={styles.symptomRow}>
          <View style={styles.symptomIcon}><Icon name={icon} size={18} color={WHITE} /></View>
          <View style={styles.flex}><Text style={styles.symptomTitle}>{t(title)}</Text><Text style={styles.symptomText}>{t(text)}</Text></View>
        </View>)}
        {topic.moreSymptoms ? <Pressable accessibilityRole="button" onPress={() => setMoreSymptoms((value) => !value)} style={styles.viewMore}><Text style={styles.viewMoreText}>{t(moreSymptoms ? 'View Less' : 'View More')}</Text></Pressable> : null}
        <Text style={styles.topicSection}>{t('Relief Tips')}</Text>
        {topic.tips.map(([title, text]) => <View key={title} style={styles.reliefCard}>
          <View style={styles.reliefTitleRow}><View style={styles.reliefIcon}><Icon name="time" size={12} color={WHITE} /></View><Text style={styles.reliefTitle}>{t(title)}</Text></View>
          <Text style={styles.reliefText}>{t(text)}</Text>
        </View>)}
        <Pressable accessibilityRole="button" onPress={exercisesPrompt} style={styles.topicButton}><Text style={styles.resultPrimaryText}>{t('Try Eye Exercises')}</Text></Pressable>
        <Pressable accessibilityRole="button" onPress={() => setDialog({ title: 'Ready to check your eye health?', message: 'This quick self-assessment will only take a few minutes.', cancel: 'Cancel', confirm: 'Next', onConfirm: onAssessment })} style={styles.topicButtonNavy}><Text style={styles.resultPrimaryText}>{t('Check My Eye Health')}</Text></Pressable>
      </ScrollView>
    </>;
  }

  if (page?.view === 'exercises') return <>
    {pageHeader('Eye Care Exercises')}
    <ScrollView style={styles.progressPage} contentContainerStyle={styles.progressDetail}>
      <View style={styles.statPair}>
        <View style={[styles.progressPanel, styles.statTile]}><Text style={styles.statTileValue}>{exercisesThisWeek}</Text><Text style={styles.statTileLabel}>{t('Exercises This Week')}</Text></View>
        <View style={[styles.progressPanel, styles.statTile]}><Text style={[styles.statTileValue, { color: ORANGE }]}>{streak}</Text><Text style={styles.statTileLabel}>{t('Day Streak')}</Text></View>
      </View>
      <Pressable accessibilityRole="button" onPress={() => onPush({ view: 'tracker' })} style={styles.progressPanel}>
        <View style={styles.progressCardHeader}><Text style={[styles.panelTitle, styles.panelTitleInline]}>{t('Progress Tracker')}</Text><Icon name="chevron-forward" size={18} color={NAVY} /></View>
        <View style={styles.progressCardHeader}><Text style={styles.noteText}>{doneToday.length} {t('of')} {exercises.length} {t('Exercises Completed')}</Text><Text style={styles.noteText}>{percent}%</Text></View>
        <View style={styles.exerciseTrack}><View style={[styles.exerciseFill, { width: `${percent}%` }]} /></View>
      </Pressable>
      {whyCard('Why Exercise Your Eyes?', 'Regular eye exercises help reduce digital eye strain, improve focus flexibility, and maintain healthy vision. These simple movements increase blood circulation to your eyes, reduce muscle tension, and can help prevent symptoms like dry eyes, blurred vision, and headaches from prolonged screen time.', 'Tip: Practice these exercises every 20 minutes during screen work for best results.')}
      <Text style={styles.topicSection}>{t('Choose an Exercise')}</Text>
      {[...exercises.filter((item) => item.id === 'focus'), ...exercises.filter((item) => item.id !== 'focus')].map((item) => <View key={item.id} style={styles.progressPanel}>
        <View style={styles.exerciseCardRow}>
          <View style={styles.exerciseIcon}><Icon name={item.icon} size={20} color={ORANGE} /></View>
          <View style={styles.flex}><Text style={styles.symptomTitle}>{t(item.card)}</Text><Text style={styles.symptomText}>{t(item.description)}</Text></View>
          {doneToday.includes(item.id) ? <Icon name="checkmark-circle" size={20} color="#1DB815" /> : null}
        </View>
        <Pressable accessibilityRole="button" onPress={() => onPush({ view: 'exercise', id: item.id })} style={styles.exerciseStart}><Text style={styles.resultHomeText}>{t(doneToday.includes(item.id) ? 'Do It Again' : 'Start Exercise')}</Text></Pressable>
      </View>)}
    </ScrollView>
  </>;

  if (page?.view === 'tracker') return <>
    {pageHeader('Progress Tracker')}
    <ScrollView style={styles.progressPage} contentContainerStyle={styles.progressDetail}>
      <Text style={styles.trackerTitle}>{t('Track Your Progress')}</Text>
      <Text style={styles.trackerSubtitle}>{t('Keep your eyes healthy with daily exercises')}</Text>
      <View style={styles.trackerCard}>
        <View style={styles.exerciseCardRow}>
          <View style={styles.exerciseIcon}><Icon name="eye" size={20} color={ORANGE} /></View>
          <View style={styles.flex}><Text style={styles.symptomTitle}>{t('Daily Eye Exercises')}</Text><Text style={styles.symptomText}>{doneToday.length} {t('of')} {exercises.length} {t('completed')}</Text></View>
          <Text style={styles.trackerCount}>{doneToday.length}/{exercises.length}</Text>
        </View>
        <View style={[styles.progressCardHeader, { marginTop: 14, marginBottom: 6 }]}><Text style={styles.noteText}>{t('Progress Today')}</Text><Text style={[styles.noteText, { color: ORANGE }]}>{percent}%</Text></View>
        <View style={styles.exerciseTrack}><View style={[styles.exerciseFill, { width: `${percent}%` }]} /></View>
        {exercises.map((item) => {
          const done = doneToday.includes(item.id);
          return <Pressable key={item.id} accessibilityRole="button" onPress={() => onPush({ view: 'exercise', id: item.id })} style={styles.trackerRow}>
            <Icon name={done ? 'checkmark-circle' : 'ellipse-outline'} size={20} color={done ? '#1DB815' : '#98A2B3'} />
            <Text style={[styles.trackerName, !done && styles.trackerPending]}>{t(item.name)}</Text>
            <Text style={[styles.trackerStatus, done && styles.trackerDone]}>{t(done ? 'Completed' : 'Pending')}</Text>
          </Pressable>;
        })}
      </View>
      <Pressable accessibilityRole="button" onPress={() => setShowHistory((value) => !value)} style={styles.historyLink}><Icon name="trending-up" size={16} color={ORANGE} /><Text style={styles.historyLinkText}>{t(showHistory ? 'Hide History' : 'View History')}</Text></Pressable>
      {showHistory ? <View style={styles.progressPanel}>{weekDates(today).filter((date) => dayKey(date) <= dayKey(today)).reverse().map((date) => <View key={dayKey(date)} style={styles.progressCardHeader}>
        <Text style={styles.noteText}>{formatDate(date, { weekday: 'long', month: 'short', day: 'numeric' })}</Text>
        <Text style={styles.weekAverage}>{(exerciseLog[dayKey(date)] || []).length}/{exercises.length}</Text>
      </View>)}</View> : null}
      <View style={styles.streakCard}>
        <View><Text style={styles.streakCardTitle}>{t('Daily Streak')}</Text><Text style={styles.streakCardText}>{t(streak ? 'Keep it up!' : 'Complete an exercise to start a streak!')}</Text></View>
        <View style={styles.alignEnd}><Text style={styles.streakCardValue}>{streak}</Text><Text style={styles.streakCardText}>{t(streak === 1 ? 'day' : 'days')}</Text></View>
      </View>
      <View style={styles.streakCardFooter}><Icon name="flame" size={14} color={ORANGE} /><Text style={styles.streakCardText}>{t('Your longest streak:')} {longestStreak} {t(longestStreak === 1 ? 'day' : 'days')}</Text></View>
    </ScrollView>
  </>;

  if (page?.view === 'exercise') {
    const exercise = exercises.find((item) => item.id === page.id);
    if (!exercise) return null;
    return <>
      {pageHeader(exercise.page)}
      <ScrollView style={styles.progressPage} contentContainerStyle={styles.progressDetail}>
        <ExerciseSession exercise={exercise} onComplete={() => onCompleteExercise(exercise.id)} onActiveChange={setSessionActive} leaveRequest={leaveRequest} onLeave={onPop} t={t} />
        {whyCard(exercise.why, exercise.whyText, exercise.tip)}
      </ScrollView>
    </>;
  }

  return <ScrollView style={styles.progressPage} contentContainerStyle={styles.progressDetail}>
    <Text style={styles.eyeCareHeading}>{t('Explore Eye Health Topics')}</Text>
    {topics.map((topic) => <View key={topic.id} style={styles.topicCard}>
      <View style={styles.topicCardIcon}><Icon name={topic.icon} size={20} color={WHITE} /></View>
      <View style={styles.flex}>
        <Text style={styles.topicCardTitle}>{t(topic.title)}</Text>
        <Text style={styles.topicCardText}>{t(topic.summary)}</Text>
        <Pressable accessibilityRole="button" onPress={() => setDialog({ title: topic.prompt[0], message: topic.prompt[1], cancel: topic.prompt[2], confirm: topic.prompt[3], onConfirm: () => onPush({ view: 'topic', id: topic.id }) })} style={styles.learnMore}><Text style={styles.learnMoreText}>{t('Learn More')}</Text></Pressable>
      </View>
    </View>)}
    <Text style={styles.eyeCareHeading}>{t('Personalize Your Eye Comfort')}</Text>
    <Pressable accessibilityRole="button" onPress={() => setDialog({ title: 'Want to customize your eye care experience?', message: 'Manage display themes, break reminders, and visual comfort tools.', cancel: 'Cancel', confirm: 'Open', onConfirm: onSettings })} style={styles.comfortCard}>
      <View style={styles.comfortIcon}><Icon name="settings" size={20} color={ORANGE} /></View>
      <View style={styles.flex}><Text style={styles.comfortTitle}>{t('Eye Care Settings')}</Text><Text style={styles.comfortText}>{t('Manage your device preferences for your eye care')}</Text></View>
      <Icon name="chevron-forward" size={20} color={WHITE} />
    </Pressable>
    <Pressable accessibilityRole="button" onPress={() => onPush({ view: 'exercises' })} style={styles.comfortCard}>
      <View style={styles.comfortIcon}><Icon name="fitness" size={20} color={ORANGE} /></View>
      <View style={styles.flex}><Text style={styles.comfortTitle}>{t('Eye Care Exercises')}</Text><Text style={styles.comfortText}>{doneToday.length} {t('of')} {exercises.length} {t('exercises completed today')}</Text></View>
      <Icon name="chevron-forward" size={20} color={WHITE} />
    </Pressable>
    <View style={styles.progressPanel}>
      <Text style={styles.panelTitle}>{t('20-20-20 break reminder')}</Text>
      <Text style={styles.noteText}>{t('Get an in-app reminder to look 20 feet away for 20 seconds every 20 minutes.')}</Text>
      <View style={styles.switchRow}><Text style={styles.bodyText}>{t(remindersOn ? 'Reminder on' : 'Reminder off')}</Text><Switch value={remindersOn} onValueChange={onReminder} trackColor={{ true: ORANGE }} /></View>
    </View>
  </ScrollView>;
  }
}

// One exercise session. Each exercise has its own layout from the design; all use the same countdown.
function ExerciseSession({ exercise, onComplete, onActiveChange, leaveRequest, onLeave, t }) {
  // 'stop' or 'leave' while the confirmation is open; the timer pauses meanwhile.
  const [confirming, setConfirming] = useState(null);
  const [resumeAfter, setResumeAfter] = useState(false);
  const [direction, setDirection] = useState('clockwise');
  const [rollSeconds, setRollSeconds] = useState(30);
  const [repetitions, setRepetitions] = useState(3);
  const [demo, setDemo] = useState(false);
  const total = exercise.id === 'rolling' ? rollSeconds * repetitions : exercise.seconds;
  const timer = useCountdown(total, onComplete);
  const nextBreak = useCountdown(30 * 60);
  const spin = useRef(new Animated.Value(0)).current;
  const running = timer.status === 'running';
  const done = timer.status === 'done';
  const active = running || timer.status === 'paused';
  useEffect(() => { onActiveChange?.(active); }, [active]);
  const askToStop = (reason) => { setResumeAfter(running); if (running) timer.pause(); setConfirming(reason); };
  const keepGoing = () => { setConfirming(null); if (resumeAfter) timer.resume(); };
  useEffect(() => { if (leaveRequest && active) askToStop('leave'); }, [leaveRequest]);
  const stopDialog = <Modal visible={confirming !== null} transparent animationType="fade" statusBarTranslucent onRequestClose={keepGoing}>
    <View style={styles.assessmentPromptBackdrop}><View style={styles.assessmentPrompt}>
      <Text style={styles.assessmentPromptTitle}>{t('Are you sure you want to stop?')}</Text>
      <Text style={[styles.assessmentPromptMessage, styles.textCenter]}>{t('Progress:')} {total - timer.remaining}s {t('completed')}</Text>
      <View style={styles.assessmentPromptActions}>
        <Pressable accessibilityRole="button" onPress={keepGoing} style={styles.assessmentPromptCancel}><Text style={styles.assessmentPromptCancelText}>{t('No')}</Text></Pressable>
        <Pressable accessibilityRole="button" onPress={() => { const reason = confirming; setConfirming(null); timer.stop(); if (reason === 'leave') onLeave(); }} style={styles.assessmentPromptNext}><Text style={styles.assessmentPromptNextText}>{t('Yes')}</Text></Pressable>
      </View>
    </View></View>
  </Modal>;

  // 30-30-30: after looking away, count down to the next break while the page is open.
  useEffect(() => { if (exercise.id === 'rule' && done) nextBreak.start(); }, [done]);
  // Eye rolling: a dot circles the ring while the exercise or the demo runs.
  useEffect(() => {
    if (exercise.id !== 'rolling' || !(running || demo)) { spin.stopAnimation(); spin.setValue(0); return undefined; }
    const loop = Animated.loop(Animated.timing(spin, { toValue: 1, duration: 4000, easing: Easing.linear, useNativeDriver: true }));
    loop.start();
    const stopDemo = demo ? setTimeout(() => setDemo(false), 8000) : null;
    return () => { loop.stop(); if (stopDemo) clearTimeout(stopDemo); };
  }, [running, demo, exercise.id]);

  const statusPill = <View style={[styles.statusPill, running && styles.statusPillActive, done && styles.statusPillDone]}>
    <Text style={[styles.statusPillText, running && styles.statusPillTextActive, done && styles.statusPillTextDone]}>{t(done ? 'Completed' : running ? 'In Progress' : timer.status === 'paused' ? 'Paused' : 'Ready')}</Text>
  </View>;
  const startButton = (style) => <Pressable accessibilityRole="button" onPress={timer.start} style={[styles.greenStart, style]}><Text style={styles.resultPrimaryText}>{t(done ? 'Start Again' : 'Start')}</Text></Pressable>;
  const controls = running || timer.status === 'paused'
    ? <View style={styles.sessionControls}>
      <Pressable accessibilityRole="button" onPress={running ? timer.pause : timer.resume} style={styles.sessionPause}><Icon name={running ? 'pause' : 'play'} size={14} color={NAVY} /><Text style={styles.sessionPauseText}>{t(running ? 'Pause' : 'Resume')}</Text></Pressable>
      <Pressable accessibilityRole="button" onPress={() => askToStop('stop')} style={styles.sessionStop}><Icon name="stop" size={14} color={WHITE} /><Text style={styles.resultHomeText}>{t('Stop')}</Text></Pressable>
    </View>
    : startButton();
  const doneNote = done ? <Text style={styles.sessionDone}>✓ {t('Exercise completed. Great job!')}</Text> : null;

  if (exercise.id === 'focus') return <View style={styles.sessionCard}>
    <View style={styles.exerciseCardRow}>
      <View style={styles.exerciseIcon}><Icon name="eye" size={20} color={ORANGE} /></View>
      <View style={styles.flex}><Text style={styles.symptomTitle}>{t('Focus Shift')}</Text><Text style={styles.symptomText}>{t('Near to Far Vision')}</Text></View>
      {statusPill}
    </View>
    <View style={styles.focusVisual}><View style={styles.focusDot} /><Icon name="swap-horizontal" size={18} color="#98A2B3" /><Text style={styles.focusTree}>🌲</Text></View>
    <Text style={styles.sessionClock}>{formatClock(timer.remaining)}</Text>
    <Text style={styles.sessionClockLabel}>{t('Time Remaining')}</Text>
    <Text style={styles.sessionInstruction}>{t('Hold your thumb 6 inches away, focus for 3 seconds, then shift to an object 10-20 feet away for 3 seconds. Repeat.')}</Text>
    {controls}{doneNote}{stopDialog}
  </View>;

  if (exercise.id === 'palming') return <View style={styles.sessionCard}>
    <View style={styles.exerciseCardRow}>
      <View style={styles.exerciseIcon}><Icon name="hand-left" size={20} color={ORANGE} />{running ? <View style={styles.liveDot} /> : null}</View>
      <View style={styles.flex}><Text style={styles.symptomTitle}>{t('Palming')}</Text><Text style={styles.symptomText}>{t(running ? 'Active Session' : done ? 'Session Complete' : 'Ready to Start')}</Text></View>
    </View>
    <Image source={require('./assets/images/exercises/palming.png')} style={styles.palmingImage} resizeMode="contain" />
    <Text style={styles.sessionClock}>{formatClock(timer.remaining)}</Text>
    <Text style={styles.sessionClockLabel}>{t('Time Remaining')}</Text>
    <View style={styles.guidanceBox}>
      <View style={styles.whyTitleRow}><Icon name="volume-medium" size={14} color={ORANGE} /><Text style={styles.guidanceTitle}>{t('Guidance')}</Text></View>
      <Text style={styles.noteText}>{t('Breathe deeply. Relax your eyes and mind.')}</Text>
    </View>
    {running ? <Pressable accessibilityRole="button" accessibilityHint={t('Stops the session')} onPress={() => askToStop('stop')} style={styles.inProgressPill}><Text style={styles.inProgressText}>●●●  {t('In Progress...')}</Text></Pressable> : startButton()}
    {doneNote}{stopDialog}
  </View>;

  if (exercise.id === 'blinking') return <View style={[styles.sessionCard, styles.centered]}>
    <View style={styles.blinkIcon}><Icon name="eye" size={36} color={WHITE} /></View>
    <Text style={styles.sessionTitle}>{t('Eye Blinking Exercise')}</Text>
    <Text style={styles.sessionSubtitle}>{t('Blink slowly and gently to moisturize your eyes')}</Text>
    <View style={styles.blinkTimer}>
      <Text style={styles.blinkClock}>{formatClock(timer.status === 'idle' ? 0 : timer.remaining)}</Text>
      <Text style={styles.sessionClockLabel}>{t(timer.status === 'idle' ? 'Tap Start to Begin' : done ? 'Well done!' : 'Time Remaining')}</Text>
    </View>
    <View style={styles.blinkInstruction}><Text style={styles.blinkInstructionText}>{t('Close your eyes gently, hold for 2 seconds, then open slowly')}</Text></View>
    {running || timer.status === 'paused' ? controls : startButton({ alignSelf: 'stretch' })}
    {doneNote}{stopDialog}
  </View>;

  if (exercise.id === 'rule') return <View style={[styles.sessionCard, styles.centered]}>
    <Text style={styles.sessionSubtitle}>{t('Prevent eye strain by looking 30 feet away every 30 minutes.')}</Text>
    <View style={styles.ruleRingOuter}><View style={[styles.ruleRingInner, running && styles.ruleRingActive]}><Text style={styles.ruleEyes}>👀</Text></View></View>
    <View style={styles.ruleNextBox}>
      <Text style={styles.noteText}>{t('Next Break In:')}</Text>
      <Text style={styles.ruleNextValue}>{formatClock(nextBreak.remaining)}</Text>
    </View>
    <View style={styles.ruleLookBox}>
      <Text style={styles.noteText}>{t('Look Away For:')}</Text>
      <Text style={styles.ruleLookValue}>{timer.remaining}s</Text>
    </View>
    {running || timer.status === 'paused' ? controls : startButton({ alignSelf: 'stretch', marginHorizontal: 24 })}
    {doneNote}{stopDialog}
  </View>;

  if (exercise.id !== 'rolling') return <View style={[styles.sessionCard, styles.centered]}>
    <View style={styles.blinkIcon}><Icon name={exercise.icon || 'eye'} size={36} color={WHITE} /></View>
    <Text style={styles.sessionTitle}>{t(exercise.card || exercise.name)}</Text>
    <Text style={styles.sessionSubtitle}>{t(exercise.description)}</Text>
    <Text style={styles.sessionClock}>{formatClock(timer.remaining)}</Text>
    <Text style={[styles.sessionClockLabel, { marginBottom: 16 }]}>{t('Time Remaining')}</Text>
    {running || timer.status === 'paused' ? controls : startButton({ alignSelf: 'stretch' })}
    {doneNote}{stopDialog}
  </View>;

  const rotate = spin.interpolate({ inputRange: [0, 1], outputRange: ['0deg', direction === 'clockwise' ? '360deg' : '-360deg'] });
  const repSeconds = timer.status === 'idle' || done ? rollSeconds : ((timer.remaining - 1) % rollSeconds) + 1;
  const currentRep = Math.min(repetitions, repetitions - Math.floor((timer.remaining - 1) / rollSeconds));
  const editable = timer.status === 'idle' || done;
  return <View style={[styles.sessionCard, styles.centered]}>
    <Text style={styles.sessionSubtitle}>{t('Gently roll your eyes in circular motions to relieve tension.')}</Text>
    <View style={styles.rollRing}>
      <Animated.View style={[styles.rollOrbit, { transform: [{ rotate }] }]}><View style={styles.rollDot} /></Animated.View>
      <View style={styles.rollInner}><Text style={styles.rollValue}>{repSeconds}s</Text>{running || timer.status === 'paused' ? <Text style={styles.symptomText}>{t('Rep')} {currentRep} {t('of')} {repetitions}</Text> : null}</View>
      <Icon name={direction === 'clockwise' ? 'refresh' : 'refresh-outline'} size={22} color={ORANGE} style={[styles.rollIcon, direction !== 'clockwise' && { transform: [{ scaleX: -1 }] }]} />
    </View>
    <View style={styles.rollOptionRow}>
      <Text style={styles.rollLabel}>{t('Direction:')}</Text>
      {[['clockwise', 'Clockwise'], ['counter', 'Counter']].map(([key, label]) => <Pressable key={key} accessibilityRole="button" disabled={!editable} onPress={() => setDirection(key)} style={[styles.directionChip, direction === key && styles.directionChipActive]}>
        <Icon name="refresh" size={12} color={direction === key ? WHITE : '#475467'} style={key === 'counter' ? { transform: [{ scaleX: -1 }] } : null} />
        <Text style={[styles.directionText, direction === key && styles.directionTextActive]}>{t(label)}</Text>
      </Pressable>)}
    </View>
    <View style={styles.rollSettingRow}>
      <Text style={styles.rollLabel}>{t('Duration:')} <Text style={styles.rollDuration}>{rollSeconds}s</Text></Text>
      <Pressable accessibilityRole="button" accessibilityLabel={t('Change duration')} disabled={!editable} onPress={() => setRollSeconds((value) => (value >= 60 ? 15 : value + 15))}><Text style={styles.rollEdit}>{t('Edit')}</Text></Pressable>
    </View>
    <View style={styles.rollSettingRow}>
      <Text style={styles.rollLabel}>{t('Repetitions:')}</Text>
      <View style={styles.inlineRow}>
        <Pressable accessibilityRole="button" accessibilityLabel={t('Fewer repetitions')} disabled={!editable} onPress={() => setRepetitions((value) => Math.max(1, value - 1))} style={styles.repButton}><Icon name="remove" size={16} color={ORANGE} /></Pressable>
        <Text style={styles.repValue}>{repetitions}x</Text>
        <Pressable accessibilityRole="button" accessibilityLabel={t('More repetitions')} disabled={!editable} onPress={() => setRepetitions((value) => Math.min(10, value + 1))} style={styles.repButton}><Icon name="add" size={16} color={ORANGE} /></Pressable>
      </View>
    </View>
    {running || timer.status === 'paused' ? controls : startButton({ alignSelf: 'stretch', marginHorizontal: 24 })}
    {editable ? <Pressable accessibilityRole="button" onPress={() => setDemo(true)}><Text style={styles.demoLink}>{t(demo ? 'Playing demo…' : 'Demo Animation')}</Text></Pressable> : null}
    {doneNote}{stopDialog}
  </View>;
}

// Tips tab: a hero, today's tip, four expandable categories (one open at a time), and facts.
function TipsPage({ categories, dailyTip, facts, t }) {
  const [open, setOpen] = useState(null);
  return <ScrollView style={styles.progressPage} contentContainerStyle={styles.progressDetail}>
    <View style={styles.tipsHero}>
      <Icon name="eye" size={56} color={ORANGE} />
      <Text style={styles.tipsHeroTitle}>{t('Protect Your Vision')}</Text>
      <Text style={styles.tipsHeroText}>{t('Discover practical tips to keep your eyes healthy and comfortable throughout your day.')}</Text>
    </View>
    <View style={[styles.progressPanel, styles.noteRow]}>
      <View style={styles.tipsBulb}><Icon name="bulb" size={18} color={WHITE} /></View>
      <View style={styles.flex}><Text style={styles.noteTitle}>{t("Today's Eye Tip")}</Text><Text style={styles.noteText}>{t(dailyTip)}</Text></View>
    </View>
    <Text style={styles.topicSection}>{t('Eye Care Tips')}</Text>
    {categories.map((category) => {
      const isOpen = open === category.id;
      return <View key={category.id} style={styles.accordion}>
        <Pressable accessibilityRole="button" accessibilityState={{ expanded: isOpen }} onPress={() => setOpen(isOpen ? null : category.id)} style={styles.accordionHeader}>
          <View style={styles.flex}>
            <View style={styles.whyTitleRow}><Icon name={category.icon} size={16} color={ORANGE} /><Text style={styles.accordionTitle}>{t(category.title)}</Text></View>
            <Text style={styles.accordionText}>{t(category.text)}</Text>
          </View>
          <Icon name={isOpen ? 'chevron-up' : 'chevron-down'} size={18} color={ORANGE} />
        </Pressable>
        {isOpen ? <View style={styles.accordionBody}>{category.items.map(([icon, color, title, text]) => <View key={title} style={styles.accordionItem}>
          <View style={styles.whyTitleRow}><Icon name={icon} size={14} color={color} /><Text style={styles.accordionItemTitle}>{t(title)}</Text></View>
          <Text style={styles.accordionItemText}>{t(text)}</Text>
        </View>)}</View> : null}
      </View>;
    })}
    {facts.length ? <View style={styles.didYouKnowRow}><View style={styles.didYouKnowIcon}><Icon name="bulb" size={16} color={ORANGE} /></View><Text style={styles.didYouKnowTitle}>{t('Did You Know?')}</Text></View> : null}
    {facts.map(([icon, text]) => <View key={text} style={styles.factCard}>
      <Icon name={icon} size={16} color={ORANGE} /><Text style={styles.factText}>{t(text)}</Text>
    </View>)}
  </ScrollView>;
}

// Settings tab: display theme switches and text contrast. Changes restyle the whole app.
function DisplaySettings({ display, onDisplay, monochrome, onMonochrome, t }) {
  const [contrast, setContrast] = useState(display.textContrast);
  useEffect(() => { setContrast(display.textContrast); }, [display.textContrast]);
  const rows = [
    ['sunny', 'Light Mode', 'Standard brightness', !display.dark, (on) => onDisplay({ dark: !on })],
    ['moon', 'Dark Mode', 'Reduces blue light', display.dark, (on) => onDisplay({ dark: on })],
    ['contrast', 'High Contrast', 'Maximum readability', display.highContrast, (on) => onDisplay({ highContrast: on })],
    ['camera', 'Monochrome', 'Turns display black and white to reduce eye fatigue.', monochrome, onMonochrome],
  ];
  return <ScrollView style={styles.progressPage} contentContainerStyle={styles.progressDetail}>
    <View style={styles.contrastBanner}>
      <View style={styles.whyTitleRow}><Icon name="eye" size={16} color={NAVY} /><Text style={styles.contrastBannerTitle}>{t('Contrast Sensitivity Issue')}</Text></View>
      <Text style={styles.contrastBannerText}>{t('Eye fatigue from DES can make it harder to distinguish between shades on digital displays')}</Text>
    </View>
    <Text style={styles.settingsHeading}>{t('Display Theme')}</Text>
    <Text style={styles.settingsSub}>{t('Choose a theme that reduces eye strain')}</Text>
    <View style={styles.themePanel}>{rows.map(([icon, title, text, value, onChange]) => <View key={title} style={styles.themeRow}>
      <View style={styles.themeIcon}><Icon name={icon} size={18} color={WHITE} /></View>
      <View style={styles.flex}><Text style={styles.themeTitle}>{t(title)}</Text><Text style={styles.themeText}>{t(text)}</Text></View>
      <Switch accessibilityLabel={t(title)} value={value} onValueChange={onChange} trackColor={{ false: paint(NAVY, 'background'), true: ORANGE }} thumbColor={WHITE} />
    </View>)}</View>
    <Text style={[styles.settingsHeading, { marginBottom: 12 }]}>{t('Contrast Adjustments')}</Text>
    <View style={styles.contrastCard}>
      <View style={styles.progressCardHeader}><Text style={styles.contrastLabel}>{t('Text Contrast')}</Text><Text style={styles.contrastLabel}>{contrast}%</Text></View>
      <Slider accessibilityLabel={t('Text Contrast')} minimumValue={50} maximumValue={100} step={1} value={display.textContrast} onValueChange={(value) => setContrast(Math.round(value))} onSlidingComplete={(value) => onDisplay({ textContrast: Math.round(value) })} minimumTrackTintColor={ORANGE} maximumTrackTintColor="#E4E7EC" thumbTintColor={ORANGE} />
      <Text style={styles.contrastHint}>{t('Higher values make text stand out more from the background.')}</Text>
    </View>
  </ScrollView>;
}

// Progress tab: an overview of four cards, each opening a detail page.
function Progress({ view, onView, dailyStats, assessments, milestoneDates, monochrome, onHistory, t }) {
  const today = new Date();
  const todayKey = dayKey(today);
  const week = weekDates(today);
  const elapsed = week.filter((date) => dayKey(date) <= todayKey);
  // Averages cover only days MataKo has data for, so days before tracking began don't count as zero.
  const tracked = Math.max(1, elapsed.filter((date) => dailyStats[dayKey(date)]).length);
  const statFor = (date, field) => dailyStats[dayKey(date)]?.[field] || 0;
  const breaksToday = statFor(today, 'breaks');
  const minutesToday = statFor(today, 'minutes');
  const weekBreaks = week.map((date) => statFor(date, 'breaks'));
  const weekMinutes = week.map((date) => statFor(date, 'minutes'));
  const breaksThisWeek = weekBreaks.reduce((sum, value) => sum + value, 0);
  const weekAverageMinutes = Math.round(weekMinutes.reduce((sum, value) => sum + value, 0) / tracked);
  const lastWeekMinutes = weekDates(today, 1).map((date) => statFor(date, 'minutes'));
  const lastWeekTracked = weekDates(today, 1).filter((date) => dailyStats[dayKey(date)]).length;
  const lastWeekAverage = lastWeekTracked ? lastWeekMinutes.reduce((sum, value) => sum + value, 0) / lastWeekTracked : 0;
  const change = lastWeekAverage > 0 ? Math.round(((weekAverageMinutes - lastWeekAverage) / lastWeekAverage) * 100) : null;
  // Current break streak: consecutive days with a break, ending today (or yesterday if none yet today).
  let breakStreak = 0;
  const streakDay = new Date(today);
  if (!breaksToday) streakDay.setDate(streakDay.getDate() - 1);
  while (statFor(streakDay, 'breaks') > 0) { breakStreak += 1; streakDay.setDate(streakDay.getDate() - 1); }
  const rows = assessments || [];
  const reached = milestoneStatus({ dailyStats, assessments, monochrome });
  const unlocked = MILESTONES.filter((item) => reached[item.id]).sort((a, b) => (milestoneDates[b.id] || '').localeCompare(milestoneDates[a.id] || ''));
  const locked = MILESTONES.filter((item) => !reached[item.id]);
  const longDate = (value) => formatDate(value, { month: 'long', day: 'numeric', year: 'numeric' });
  const shortDate = (value) => formatDate(value, { month: '2-digit', day: '2-digit', year: '2-digit' });
  const severity = (item) => SEVERITY[item.risk_level] || SEVERITY.LOW;

  const pageHeader = (title) => <View style={styles.progressHeader}>
    <Pressable accessibilityRole="button" accessibilityLabel={t('Back')} hitSlop={10} onPress={() => onView('overview')} style={styles.progressBack}><Icon name="arrow-back" size={22} color={NAVY} /></Pressable>
    <Text style={styles.progressHeaderTitle}>{t(title)}</Text>
    <View style={styles.progressBack} />
  </View>;
  const sectionCard = (key, title, icon, onPress, children) => <Pressable key={key} accessibilityRole="button" onPress={onPress} style={styles.progressCard}>
    <View style={styles.progressCardHeader}><Text style={styles.progressCardTitle}>{t(title)}</Text><Icon name={icon} size={16} color={ORANGE} /></View>
    {children}
  </Pressable>;
  const bars = (values, { height, max, color, labels, labelStyle, overlay }) => <View>
    <View style={[styles.barRow, { height }]}>{overlay}{values.map((value, index) => <View key={WEEK_DAYS[index]} style={styles.barSlot}>
      <View style={[styles.bar, { height: Math.max(value > 0 ? 3 : 0, (value / max) * height), backgroundColor: typeof color === 'function' ? color(index, value) : color }]} />
    </View>)}</View>
    <View style={styles.barLabels}>{(labels || WEEK_DAYS).map((label, index) => <Text key={`${label}${index}`} style={[styles.barLabel, labelStyle]}>{t(label)}</Text>)}</View>
  </View>;

  if (view === 'breaks') {
    const max = Math.max(10, Math.ceil(Math.max(...weekBreaks) / 2) * 2);
    const ticks = [5, 4, 3, 2, 1, 0].map((step) => (max / 5) * step);
    const best = Math.max(...weekBreaks);
    return <>
      {pageHeader('Your Weekly Screen Breaks')}
      <ScrollView style={styles.progressPage} contentContainerStyle={styles.progressDetail}>
        <View style={styles.progressPanel}>
          <View style={styles.chartArea}>
            <View style={styles.chartAxis}>{ticks.map((tick, index) => <Text key={tick} style={[styles.chartTick, styles.chartAxisTick, { top: (index / 5) * 190 - 7 }]}>{tick}</Text>)}</View>
            <View style={styles.flex}>
              <View style={styles.chartGrid}>{ticks.map((tick, index) => <View key={tick} style={[styles.chartGridLine, { top: (index / 5) * 190 }]} />)}</View>
              {bars(weekBreaks, { height: 190, max, color: ORANGE, labelStyle: styles.chartDayLabel })}
            </View>
          </View>
          <Text style={styles.chartCaption}>{t('Days of the Week')}</Text>
        </View>
        <View style={[styles.progressPanel, styles.centered]}>
          <Text style={styles.breakTotal}>{breaksThisWeek}</Text>
          <Text style={styles.progressBody}>{t("You've taken")} {breaksThisWeek} {t(breaksThisWeek === 1 ? 'break this week.' : 'breaks this week.')}</Text>
          <View style={styles.streakBox}><Text style={styles.streakText}>{breakStreak > 0 ? `🔥${breakStreak}-${t(breakStreak === 1 ? 'day break streak!' : 'day break streak!')}` : t('Take a break today to start a streak!')}</Text></View>
        </View>
        <View style={styles.statPair}>
          <View style={[styles.progressPanel, styles.statTile]}><Text style={styles.statTileValue}>{(breaksThisWeek / tracked).toFixed(1)}</Text><Text style={styles.statTileLabel}>{t('Daily Average')}</Text></View>
          <View style={[styles.progressPanel, styles.statTile]}><Text style={styles.statTileValue}>{best}</Text><Text style={styles.statTileLabel}>{t('Best Day')}</Text></View>
        </View>
        <View style={[styles.progressPanel, styles.noteRow]}>
          <View style={styles.noteIcon}><Icon name="bulb" size={16} color={WHITE} /></View>
          <View style={styles.flex}><Text style={styles.noteTitle}>{t('Keep it up!')}</Text><Text style={styles.noteText}>{t('Taking regular breaks helps reduce eye strain and improves focus. Try to maintain your streak!')}</Text></View>
        </View>
      </ScrollView>
    </>;
  }

  if (view === 'screenTime') {
    const max = Math.max(SCREEN_TIME_GOAL_MINUTES * 1.6, ...weekMinutes);
    const height = 110;
    const trackedDays = elapsed.filter((date) => dailyStats[dayKey(date)]);
    const lessThanAverage = trackedDays.filter((date) => statFor(date, 'minutes') < weekAverageMinutes).length;
    return <>
      {pageHeader('Your Screen Time')}
      <ScrollView style={styles.progressPage} contentContainerStyle={styles.progressDetail}>
        <View style={styles.progressPanel}>
          <Text style={styles.panelTitle}>{t("Today's Usage")}</Text>
          <View style={styles.usageRing}><Text style={styles.usageValue}>{formatMinutes(minutesToday)}</Text><Text style={styles.usageGoal}>{t('of 3h goal')}</Text></View>
          <View style={styles.usageTrack}><View style={[styles.usageFill, { width: `${Math.min(100, (minutesToday / SCREEN_TIME_GOAL_MINUTES) * 100)}%` }]} /></View>
          <View style={styles.usageScale}><Text style={styles.chartTick}>0h</Text><Text style={styles.chartTick}>{t('3h goal')}</Text></View>
        </View>
        <View style={styles.progressPanel}>
          <Text style={styles.panelTitle}>{t('Past 7 Days')}</Text>
          <View>
            {bars(weekMinutes, {
              height, max,
              color: (index, value) => (dayKey(week[index]) === todayKey ? '#E06A00' : value > SCREEN_TIME_GOAL_MINUTES ? '#FFBF8A' : ORANGE),
              labels: WEEK_DAYS,
              overlay: <View style={[styles.goalLine, { bottom: (SCREEN_TIME_GOAL_MINUTES / max) * height }]}><Text style={styles.goalLabel}>{t('3h goal')}</Text></View>,
            })}
          </View>
          <View style={styles.barLabels}>{weekMinutes.map((value, index) => <Text key={WEEK_DAYS[index]} style={styles.barHours}>{week[index] > today ? '' : `${Number((value / 60).toFixed(1))}h`}</Text>)}</View>
        </View>
        <View style={[styles.progressPanel, styles.averageRow]}>
          <View><Text style={styles.averageLabel}>{t('Weekly Average')}</Text><Text style={styles.averageValue}>{formatMinutes(weekAverageMinutes)}</Text></View>
          <View style={styles.alignEnd}><Text style={styles.averageLabel}>{t('Change')}</Text><Text style={styles.changeValue}>{change === null ? '—' : `${change > 0 ? '+' : ''}${change}%`}</Text></View>
        </View>
        <View style={[styles.progressPanel, styles.noteRow]}>
          <View style={styles.noteIcon}><Icon name="trending-up" size={16} color={WHITE} /></View>
          <View style={styles.flex}><Text style={styles.noteTitle}>{t('Progress Summary')}</Text><Text style={styles.noteText}>{t('You used your screen less than average on')} {lessThanAverage} {t('of the past')} {trackedDays.length} {t(trackedDays.length === 1 ? 'day.' : 'days.')}</Text></View>
        </View>
      </ScrollView>
    </>;
  }

  if (view === 'eyeHealth') return <>
    {pageHeader('Your Eye Health Over Time')}
    <ScrollView style={styles.progressPage} contentContainerStyle={styles.progressDetail}>
      {rows.length ? rows.map((item) => <Pressable key={item.id} accessibilityRole="button" onPress={() => onHistory(item.id)} style={[styles.progressPanel, styles.historyRow]}>
        <View style={[styles.severityDot, { backgroundColor: severity(item)[1] }]} />
        <View style={styles.flex}><Text style={styles.historyDate}>{longDate(item.created_at)}</Text><Text style={styles.historyLevel}>{t(severity(item)[0])}</Text></View>
        <Icon name="chevron-forward" size={18} color="#98A2B3" />
      </Pressable>) : <View style={styles.progressPanel}><Text style={styles.noteText}>{t('Complete a self-assessment to see your progress here.')}</Text></View>}
      <View style={styles.progressPanel}>
        <Text style={styles.noteTitle}>{t('Severity Levels')}</Text>
        {Object.values(SEVERITY).map(([label, color]) => <View key={label} style={styles.legendRow}><View style={[styles.severityDot, { backgroundColor: color }]} /><Text style={styles.noteText}>{t(label)}</Text></View>)}
      </View>
    </ScrollView>
  </>;

  if (view === 'milestones') {
    const badge = (item, isUnlocked) => <View key={item.id} style={[styles.badgeCard, !isUnlocked && styles.badgeLocked]}>
      <View style={[styles.badgeIcon, { backgroundColor: isUnlocked ? item.color : '#D0D5DD' }]}><Icon name={item.icon} size={20} color={isUnlocked ? WHITE : '#667085'} /></View>
      <Text style={[styles.badgeTitle, !isUnlocked && styles.badgeTitleLocked]}>{t(item.title)}</Text>
      <Text style={styles.badgeDate}>{isUnlocked ? `${t('Unlocked')} ${formatDate(`${milestoneDates[item.id] || todayKey}T00:00:00`, { month: 'short', day: 'numeric', year: 'numeric' })}` : t(item.hint)}</Text>
      {isUnlocked ? <View style={styles.badgeUnderline} /> : null}
    </View>;
    return <>
      {pageHeader('Your Milestones')}
      <ScrollView style={styles.progressPage} contentContainerStyle={styles.progressDetail}>
        <View style={styles.progressPanel}>
          <View style={styles.progressCardHeader}><Text style={[styles.panelTitle, styles.panelTitleInline]}>{t('Achievement Progress')}</Text><Text style={styles.achievementCount}>{unlocked.length}/{MILESTONES.length}</Text></View>
          <View style={styles.usageTrack}><View style={[styles.usageFill, { width: `${(unlocked.length / MILESTONES.length) * 100}%` }]} /></View>
          <Text style={styles.achievementHint}>{locked.length ? `${locked.length} ${t(locked.length === 1 ? 'more achievement to unlock' : 'more achievements to unlock')}` : t('All achievements unlocked!')}</Text>
        </View>
        <Text style={styles.badgeSection}>{t('Unlocked Achievements')}</Text>
        <View style={styles.badgeGrid}>{unlocked.map((item) => badge(item, true))}</View>
        {locked.length ? <><Text style={styles.badgeSection}>{t('Coming Up Next')}</Text><View style={styles.badgeGrid}>{locked.map((item) => badge(item, false))}</View></> : null}
      </ScrollView>
    </>;
  }

  const maxBreaks = Math.max(1, ...weekBreaks);
  return <ScrollView contentContainerStyle={styles.progressOverview}>
    {sectionCard('breaks', 'Screen Breaks', 'eye', () => onView('breaks'), <>
      <Text style={styles.progressBody}>{t("You've taken")} {breaksToday} {t(breaksToday === 1 ? 'break today' : 'breaks today')}</Text>
      <View style={styles.miniChart}>{breaksThisWeek ? bars(weekBreaks, { height: 80, max: maxBreaks, color: ORANGE, labels: WEEK_DAYS.map(() => '') }) : <View style={styles.miniChartEmpty}><Icon name="analytics" size={22} color="#98A2B3" /><Text style={styles.miniChartText}>{t('7-day break activity')}</Text></View>}</View>
      <View style={styles.barLabels}>{WEEK_DAYS.map((label) => <Text key={label} style={styles.barLabel}>{t(label)}</Text>)}</View>
    </>)}
    {sectionCard('screenTime', "Today's Screen Time", 'time', () => onView('screenTime'), <>
      <View style={styles.timeCircle}><Text style={styles.usageValue}>{formatMinutes(minutesToday)}</Text><Text style={styles.usageGoal}>{t('of 3h goal')}</Text></View>
      <View style={styles.divider} />
      <View style={styles.progressCardHeader}><Text style={styles.noteText}>{t('This week avg')}</Text><Text style={styles.weekAverage}>{formatMinutes(weekAverageMinutes)}</Text></View>
    </>)}
    {sectionCard('eyeHealth', 'Your Eye Health Over Time', 'stats-chart', () => onView('eyeHealth'), rows.length ? rows.slice(0, 2).map((item) => <View key={item.id} style={styles.overviewHistoryRow}>
      <View style={[styles.severityDot, { backgroundColor: severity(item)[1] }]} /><Text style={[styles.progressBody, styles.flex]}>{t(severity(item)[0])}</Text><Text style={styles.chartTick}>{shortDate(item.created_at)}</Text>
    </View>) : <Text style={styles.noteText}>{t('Complete a self-assessment to see your progress here.')}</Text>)}
    {sectionCard('milestones', 'Your Milestones', 'trophy', () => onView('milestones'), <>
      <View style={styles.progressCardHeader}><Text style={styles.progressBody}>{unlocked.length} {t('of')} {MILESTONES.length} {t('achievements unlocked')}</Text></View>
      <View style={styles.usageTrack}><View style={[styles.usageFill, { width: `${(unlocked.length / MILESTONES.length) * 100}%` }]} /></View>
    </>)}
  </ScrollView>;
}

// An introduction, a rating guide, then one question per screen.
function SelfAssessment({ questions, introImage, name, onBack, onSubmit, loading, t, header }) {
  const [step, setStep] = useState('intro');
  const [answers, setAnswers] = useState({});

  if (step === 'intro') return <>
    {header(t('Self-Assessment'), onBack)}
    <View style={styles.studentIntro}>
      <Text style={styles.studentIntroTitle}>{t('Quick Eye Check-Up')}</Text>
      <Text style={styles.studentIntroText}>{t('Hi, ')}{name || t('User')}{t("! I'll ask you questions to check on your eye health. Before we begin, I'll give you a quick guide on how to answer. Ready? Let's go!")}</Text>
      <Image source={introImage} style={styles.studentIntroImage} resizeMode="contain" />
      <Pressable onPress={() => setStep('guide')} style={styles.studentProceed}><Text style={styles.buttonText}>{t('Proceed')}</Text></Pressable>
    </View>
  </>;

  if (step === 'guide') return <>
    {header(t('How to Answer'), () => setStep('intro'))}
    <ScrollView contentContainerStyle={styles.studentGuide}>
      <Image source={require('./assets/images/assessment/guide.png')} style={styles.studentGuideImage} resizeMode="contain" />
      <View style={styles.studentGuideCard}>
        <Text style={styles.studentGuideText}>{t('Before we begin, please review this rating scale. It will guide your answers.')}</Text>
        <Text style={[styles.studentGuideText, { marginTop: 16 }]}>{t('Choose the option that best matches how often you experience each symptom:')}</Text>
        {ANSWER_GUIDE.map(([label, description]) => <View key={label} style={styles.studentGuideRow}>
          <Text style={styles.studentGuideLabel}>{t(label)}</Text>
          <Text style={styles.studentGuideDescription}>{t(description)}</Text>
        </View>)}
      </View>
      <Pressable onPress={() => setStep(0)} style={styles.studentContinue}><Text style={styles.studentContinueText}>{t('Continue')}  →</Text></Pressable>
    </ScrollView>
  </>;

  const question = questions[step];
  const last = step === questions.length - 1;
  const selected = answers[question.symptom];
  // The design starts at 0% and stops at 99% on the final question.
  const progress = Math.min(99, Math.round((step / Math.max(1, questions.length - 1)) * 100));
  const goBack = () => setStep(step === 0 ? 'guide' : step - 1);
  const goNext = () => (last ? onSubmit(answers) : setStep(step + 1));

  return <>
    {header(t('Self-Assessment'), goBack)}
    <View style={styles.fill}>
      <View style={styles.studentQuestionTop}>
        <View style={styles.studentProgressLabels}>
          <Text style={styles.studentProgressText}>{t('Question')} {step + 1} {t('of')} {questions.length}</Text>
          <Text style={styles.studentProgressText}>{progress}%</Text>
        </View>
        <View style={styles.studentProgressTrack}><View style={[styles.studentProgressFill, { width: `${Math.max(progress, 2)}%` }]} /></View>
        <Image source={question.image} style={styles.studentQuestionImage} resizeMode="contain" />
      </View>
      <ScrollView style={styles.studentQuestionBody} contentContainerStyle={styles.studentQuestionContent}>
        <View style={styles.studentQuestionCard}><Text style={styles.studentQuestionText}>{t(question.question)}</Text></View>
        {ANSWER_OPTIONS.map((option) => <Pressable key={option.label} accessibilityRole="radio" accessibilityState={{ checked: selected === option.value }} onPress={() => setAnswers((current) => ({ ...current, [question.symptom]: option.value }))} style={[styles.studentOption, selected === option.value && styles.studentOptionSelected]}>
          <View style={[styles.studentRadio, selected === option.value && styles.studentRadioSelected]}>{selected === option.value ? <View style={styles.studentRadioDot} /> : null}</View>
          <Text style={styles.studentOptionText}>{t(option.label)}</Text>
        </Pressable>)}
      </ScrollView>
      <View style={styles.studentFooter}>
        <Pressable onPress={goBack} style={styles.studentBack}><Text style={styles.studentBackText}>{t('Back')}</Text></Pressable>
        <Pressable onPress={goNext} disabled={selected === undefined || loading} style={[styles.studentNext, (selected === undefined || loading) && styles.disabled]}><Text style={styles.buttonText}>{loading ? t('Saving…') : t('Next')}</Text></Pressable>
      </View>
    </View>
  </>;
}

// Result: a summary, a scoring breakdown, and care tips matched to the strain level.
function AssessmentResult({ assessment, results, t, header, onRetake, onHome }) {
  const [view, setView] = useState('summary');
  const [tipsOrigin, setTipsOrigin] = useState('summary');
  const levelKey = ASSESSMENT_RESULTS[assessment.risk_level] ? assessment.risk_level : 'LOW';
  const level = { ...ASSESSMENT_RESULTS[levelKey], ...(results?.[levelKey] || {}) };
  const score = assessment.total_score || 0;
  const max = maxScore(assessment);
  const openTips = (from) => { setTipsOrigin(from); setView('tips'); };
  const footer = (homeStyle) => <View style={styles.resultFooterRow}>
    <Pressable onPress={onRetake} style={styles.resultRetake}><Text style={styles.resultRetakeText}>{t('Retake Assessment')}</Text></Pressable>
    <Pressable onPress={onHome} style={[styles.resultHome, homeStyle]}><Text style={styles.resultHomeText}>{t('Back to Home')}</Text></Pressable>
  </View>;

  if (view === 'breakdown') return <>
    {header(t('Scoring Breakdown'), () => setView('summary'))}
    <ScrollView style={styles.resultPage} contentContainerStyle={styles.breakdownContent}>
      <View style={styles.breakdownTop}>
        <View style={styles.resultTopCard}>
          <View style={styles.resultIconCircle}><Icon name="eye" size={24} color={ORANGE} /></View>
          <Text style={styles.breakdownTitle}>{t('Your Eye Strain Assessment')}</Text>
          <View style={styles.breakdownLevelBox}>
            <Text style={[styles.breakdownLevel, { color: level.color }]}>{t(level.label)}</Text>
            <Text style={styles.breakdownScoreLine}><Text style={styles.bold}>{t('Score:')} </Text><Text style={[styles.bold, { color: level.color }]}>{score}</Text> {t('out of')} {max} {t('points')}</Text>
          </View>
        </View>
      </View>
      <View style={styles.breakdownBody}>
        <Text style={styles.breakdownHeading}>📊 {t('How We Calculated Your Result')}</Text>
        <View style={styles.breakdownCard}>
          <Text style={styles.breakdownCardTitle}>{t('Scoring System')}</Text>
          <Text style={styles.breakdownSmall}>{t('Each answer is scored based on frequency:')}</Text>
          {[['Never', '#138A07', '0 point'], ['Occasionally', level.occasionallyColor, '1 point'], ['Often or Always', '#E5121B', '2 points']].map(([label, color, points]) => <View key={label} style={styles.breakdownRow}>
            <Text style={[styles.breakdownOption, { color }]}>{t(label)}</Text><Text style={styles.breakdownSmall}>{t(points)}</Text>
          </View>)}
        </View>
        <View style={styles.breakdownCard}>
          <Text style={styles.breakdownSmall}>{t('With')} {max / 2} {t('questions, maximum total is')} {max} {t('points')}</Text>
          <View style={styles.breakdownRow}><Text style={styles.breakdownSmall}>{t('Your Score')}</Text><Text style={styles.breakdownSmall}>{score} {t('points')}</Text></View>
          <View style={styles.breakdownTrack}><View style={[styles.breakdownFill, { width: `${(score / max) * 100}%`, backgroundColor: level.color }]} /></View>
          <View style={styles.breakdownRow}>{[0, max / 2, max].map((tick) => <Text key={tick} style={styles.breakdownTick}>{tick}</Text>)}</View>
        </View>
        <Pressable onPress={() => openTips('breakdown')} style={styles.breakdownTipsButton}><Text style={styles.resultPrimaryText}>{t('View Care Tips')}</Text></Pressable>
        {footer(styles.resultHomeNavy)}
      </View>
    </ScrollView>
  </>;

  if (view === 'tips') return <>
    {header(t('Care Tips'), () => setView(tipsOrigin))}
    <ScrollView style={styles.resultPage} contentContainerStyle={styles.tipsContent}>
      <View style={styles.resultTopCard}>
        <View style={[styles.resultIconCircle, level.urgent && styles.tipsUrgentCircle]}><Icon name={level.urgent ? 'warning' : 'eye'} size={24} color={level.urgent ? '#F5C518' : ORANGE} /></View>
        <Text style={[styles.tipsTitle, level.urgent && styles.bold]}>{t(level.tipsTitle)}</Text>
        <Text style={styles.tipsIntro}>{t(level.tipsIntro)}</Text>
      </View>
      {level.tips.map(([icon, title, text]) => <View key={title} style={styles.tipCard}>
        <View style={styles.tipIcon}><Icon name={icon} size={18} color={WHITE} /></View>
        <View style={styles.flex}><Text style={styles.tipTitle}>{t(title)}</Text><Text style={styles.tipText}>{t(text)}</Text></View>
      </View>)}
      {level.note ? <View style={styles.tipNote}><Icon name="information-circle" size={16} color={NAVY} /><Text style={styles.tipNoteText}>{t(level.note)}</Text></View> : null}
      <Pressable onPress={() => setView(tipsOrigin)} style={styles.tipsBack}><Text style={styles.resultHomeText}>{t('Back to Results')}</Text></Pressable>
    </ScrollView>
  </>;

  return <>
    {header(t('Assessment Complete'), onHome)}
    <View style={styles.resultPage}>
      <ScrollView contentContainerStyle={styles.resultSummary}>
        <Image source={level.image} style={styles.resultMascot} resizeMode="contain" />
        <Text style={styles.resultHeadline}>{t('You have ')}<Text style={[styles.bold, { color: level.color }]}>{t(level.label)}</Text></Text>
        <Text style={styles.resultMessage}>{t(level.message)}</Text>
        <Pressable onPress={() => openTips('summary')} style={styles.resultPrimary}><Text style={styles.resultPrimaryText}>{t('View Care Tips')}</Text></Pressable>
        <Pressable onPress={() => setView('breakdown')} style={styles.resultSecondary}><Text style={styles.resultSecondaryText}>{t('View Scoring Breakdown')}</Text></Pressable>
      </ScrollView>
      <View style={styles.resultFooter}>{footer()}</View>
    </View>
  </>;
}

const styleDefs = {
  grayscale: { filter: [{ grayscale: 1 }] },
  tipsHero: { backgroundColor: NAVY, borderRadius: 8, alignItems: 'center', paddingHorizontal: 24, paddingVertical: 26, marginBottom: 14 }, tipsHeroTitle: { color: WHITE, fontSize: 17, fontWeight: '700', marginTop: 8, marginBottom: 10 }, tipsHeroText: { color: WHITE, fontSize: 12, lineHeight: 18, textAlign: 'center' }, tipsBulb: { width: 34, height: 34, borderRadius: 17, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', marginRight: 12 },
  accordion: { marginBottom: 10 }, accordionHeader: { flexDirection: 'row', alignItems: 'center', backgroundColor: WHITE, borderRadius: 6, borderWidth: 1, borderColor: '#ECECEC', padding: 14 }, accordionTitle: { color: NAVY, fontSize: 15, fontWeight: '600', marginLeft: 8 }, accordionText: { color: '#475467', fontSize: 11, lineHeight: 16, marginRight: 10 }, accordionBody: { backgroundColor: '#E9E9E9', marginHorizontal: 8, padding: 8, borderBottomLeftRadius: 6, borderBottomRightRadius: 6 }, accordionItem: { backgroundColor: WHITE, borderRadius: 6, padding: 12, marginBottom: 8 }, accordionItemTitle: { color: NAVY, fontSize: 13, fontWeight: '500', marginLeft: 8 }, accordionItemText: { color: '#475467', fontSize: 11, lineHeight: 16, paddingLeft: 22 },
  didYouKnowRow: { flexDirection: 'row', alignItems: 'center', marginTop: 10, marginBottom: 10 }, didYouKnowIcon: { width: 28, height: 28, borderRadius: 6, backgroundColor: '#FFE1C8', alignItems: 'center', justifyContent: 'center', marginRight: 10 }, didYouKnowTitle: { color: NAVY, fontSize: 16, fontWeight: '600' }, factCard: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#FBEFE4', borderWidth: 1, borderColor: '#F6D9BF', borderRadius: 6, padding: 14, marginBottom: 10 }, factText: { flex: 1, color: NAVY, fontSize: 13, lineHeight: 19, marginLeft: 10 },
  contrastBanner: { backgroundColor: '#F2F4F7', borderLeftWidth: 3, borderLeftColor: ORANGE, borderRadius: 4, padding: 14, marginBottom: 20 }, contrastBannerTitle: { color: NAVY, fontSize: 13, fontWeight: '600', marginLeft: 8 }, contrastBannerText: { color: NAVY, fontSize: 11, lineHeight: 16, paddingLeft: 24 }, settingsHeading: { color: NAVY, fontSize: 16, fontWeight: '700' }, settingsSub: { color: '#475467', fontSize: 11, marginTop: 2, marginBottom: 12 },
  themePanel: { backgroundColor: WHITE, borderRadius: 6, borderWidth: 1, borderColor: '#E4E7EC', padding: 10, marginBottom: 22 }, themeRow: { flexDirection: 'row', alignItems: 'center', borderWidth: 1, borderColor: NAVY, borderRadius: 8, padding: 12, marginBottom: 10 }, themeIcon: { width: 40, height: 40, borderRadius: 4, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', marginRight: 12 }, themeTitle: { color: NAVY, fontSize: 14, fontWeight: '500' }, themeText: { color: NAVY, fontSize: 11, lineHeight: 15, marginRight: 8 }, contrastCard: { backgroundColor: NAVY, borderRadius: 8, padding: 16 }, contrastLabel: { color: WHITE, fontSize: 13, fontWeight: '600' }, contrastHint: { color: '#D0D5DD', fontSize: 11, marginTop: 6 },
  eyeCareHeading: { color: NAVY, fontSize: 18, fontWeight: '600', textAlign: 'center', marginTop: 8, marginBottom: 16 }, topicCard: { flexDirection: 'row', backgroundColor: WHITE, borderRadius: 6, borderWidth: 1, borderColor: '#ECECEC', padding: 18, marginBottom: 14, elevation: 1 }, topicCardIcon: { width: 38, height: 38, borderRadius: 4, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', marginRight: 14 }, topicCardTitle: { color: NAVY, fontSize: 15, marginBottom: 8 }, topicCardText: { color: '#475467', fontSize: 13, lineHeight: 19 }, learnMore: { alignSelf: 'flex-start', backgroundColor: NAVY, borderRadius: 4, paddingHorizontal: 14, paddingVertical: 9, marginTop: 14 }, learnMoreText: { color: WHITE, fontSize: 13 },
  comfortCard: { flexDirection: 'row', alignItems: 'center', backgroundColor: NAVY, borderRadius: 8, paddingHorizontal: 18, paddingVertical: 20, marginBottom: 12 }, comfortIcon: { width: 40, height: 40, borderRadius: 20, backgroundColor: WHITE, alignItems: 'center', justifyContent: 'center', marginRight: 14 }, comfortTitle: { color: WHITE, fontSize: 17, fontWeight: '600', marginBottom: 4 }, comfortText: { color: '#D0D5DD', fontSize: 12, lineHeight: 17 },
  topicHero: { backgroundColor: WHITE, borderRadius: 6, borderWidth: 1, borderColor: '#ECECEC', alignItems: 'center', padding: 22, marginBottom: 20 }, topicTitle: { color: NAVY, fontSize: 21, fontWeight: '500', textAlign: 'center', marginTop: 6, marginBottom: 10 }, topicSubtitle: { color: '#475467', fontSize: 13, lineHeight: 19, textAlign: 'center' }, topicDivider: { alignSelf: 'stretch', height: 1, backgroundColor: '#E4E7EC', marginVertical: 20 }, topicImpactRow: { alignSelf: 'stretch', flexDirection: 'row', alignItems: 'center', marginBottom: 10 }, topicCheck: { width: 26, height: 26, borderRadius: 13, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', marginRight: 12 }, topicImpactTitle: { color: NAVY, fontSize: 14, fontWeight: '600', flex: 1 }, topicImpactText: { alignSelf: 'stretch', color: '#475467', fontSize: 13, lineHeight: 19, paddingLeft: 38 },
  topicSection: { color: NAVY, fontSize: 17, fontWeight: '600', marginTop: 4, marginBottom: 12 }, symptomRow: { flexDirection: 'row', alignItems: 'center', backgroundColor: WHITE, borderRadius: 6, borderWidth: 1, borderColor: '#ECECEC', padding: 14, marginBottom: 10 }, symptomIcon: { width: 34, height: 34, borderRadius: 4, backgroundColor: NAVY, alignItems: 'center', justifyContent: 'center', marginRight: 14 }, symptomTitle: { color: NAVY, fontSize: 15, fontWeight: '600', marginBottom: 3 }, symptomText: { color: '#475467', fontSize: 12, lineHeight: 17 },
  reliefCard: { backgroundColor: NAVY, borderRadius: 6, padding: 16, marginBottom: 10 }, reliefTitleRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 6 }, reliefIcon: { width: 20, height: 20, borderRadius: 10, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', marginRight: 10 }, reliefTitle: { color: WHITE, fontSize: 15, fontWeight: '600' }, reliefText: { color: '#D0D5DD', fontSize: 13, lineHeight: 19, paddingLeft: 30 }, topicButton: { minHeight: 50, borderRadius: 8, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', marginTop: 10, elevation: 3 }, topicButtonNavy: { minHeight: 50, borderRadius: 8, backgroundColor: NAVY, alignItems: 'center', justifyContent: 'center', marginTop: 12 }, viewMore: { alignSelf: 'center', backgroundColor: ORANGE, borderRadius: 14, paddingHorizontal: 18, paddingVertical: 7, marginTop: 4, marginBottom: 14 }, viewMoreText: { color: WHITE, fontSize: 12, fontWeight: '600' }, textCenter: { textAlign: 'center' },
  whyCard: { backgroundColor: WHITE, borderRadius: 4, borderWidth: 1, borderColor: '#ECECEC', padding: 16, marginBottom: 12 }, whyTitleRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 8 }, whyTitle: { color: NAVY, fontSize: 16, fontWeight: '600', marginLeft: 8, flex: 1 }, whyText: { color: '#475467', fontSize: 13, lineHeight: 21 }, tipCallout: { flexDirection: 'row', alignItems: 'flex-start', backgroundColor: '#FFF8F1', borderLeftWidth: 3, borderLeftColor: ORANGE, borderRadius: 4, padding: 12, marginTop: 14 }, tipCalloutText: { flex: 1, color: '#475467', fontSize: 11, lineHeight: 16, marginLeft: 8 },
  exerciseTrack: { height: 8, borderRadius: 4, backgroundColor: '#E4E7EC', overflow: 'hidden' }, exerciseFill: { height: 8, borderRadius: 4, backgroundColor: ORANGE }, exerciseCardRow: { flexDirection: 'row', alignItems: 'center' }, exerciseIcon: { width: 40, height: 40, borderRadius: 20, backgroundColor: '#FFEBDA', alignItems: 'center', justifyContent: 'center', marginRight: 12 }, exerciseStart: { minHeight: 42, borderRadius: 6, backgroundColor: NAVY, alignItems: 'center', justifyContent: 'center', marginTop: 14 },
  trackerTitle: { color: NAVY, fontSize: 21, fontWeight: '700', textAlign: 'center', marginTop: 10 }, trackerSubtitle: { color: '#475467', fontSize: 14, lineHeight: 21, textAlign: 'center', marginTop: 8, marginBottom: 20, paddingHorizontal: 30 }, trackerCard: { backgroundColor: WHITE, borderRadius: 10, padding: 18, marginBottom: 6, elevation: 4 }, trackerCount: { color: ORANGE, fontSize: 22, fontWeight: '700' }, trackerRow: { flexDirection: 'row', alignItems: 'center', paddingVertical: 12 }, trackerName: { flex: 1, color: NAVY, fontSize: 13, marginLeft: 12 }, trackerPending: { color: '#475467' }, trackerStatus: { color: '#667085', fontSize: 11 }, trackerDone: { color: '#16A34A' },
  historyLink: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', paddingVertical: 16 }, historyLinkText: { color: ORANGE, fontSize: 14, marginLeft: 6 }, streakCard: { flexDirection: 'row', justifyContent: 'space-between', backgroundColor: NAVY, borderTopLeftRadius: 10, borderTopRightRadius: 10, paddingHorizontal: 20, paddingTop: 20, paddingBottom: 6, marginTop: 6 }, streakCardFooter: { flexDirection: 'row', alignItems: 'center', backgroundColor: NAVY, borderBottomLeftRadius: 10, borderBottomRightRadius: 10, paddingHorizontal: 20, paddingBottom: 20, paddingTop: 8 }, streakCardTitle: { color: WHITE, fontSize: 16, fontWeight: '600', marginBottom: 4 }, streakCardText: { color: '#D0D5DD', fontSize: 12, marginLeft: 0 }, streakCardValue: { color: WHITE, fontSize: 22, fontWeight: '700' },
  sessionCard: { backgroundColor: WHITE, borderRadius: 10, borderWidth: 1.5, borderColor: NAVY, padding: 18, marginBottom: 16, elevation: 3 }, statusPill: { backgroundColor: '#F2F4F7', borderRadius: 12, paddingHorizontal: 10, paddingVertical: 4 }, statusPillActive: { backgroundColor: '#DCFCE7' }, statusPillDone: { backgroundColor: '#E0F2FE' }, statusPillText: { color: '#475467', fontSize: 11 }, statusPillTextActive: { color: '#15803D' }, statusPillTextDone: { color: '#0369A1' },
  focusVisual: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-evenly', backgroundColor: '#F7F7F7', borderRadius: 6, paddingVertical: 14, paddingHorizontal: 60, marginVertical: 16 }, focusDot: { width: 18, height: 18, borderRadius: 9, backgroundColor: ORANGE }, focusTree: { fontSize: 18 },
  sessionClock: { color: ORANGE, fontSize: 26, fontWeight: '700', textAlign: 'center' }, sessionClockLabel: { color: '#475467', fontSize: 12, textAlign: 'center', marginTop: 4 }, sessionInstruction: { color: NAVY, fontSize: 13, lineHeight: 20, marginVertical: 16 }, sessionControls: { flexDirection: 'row', marginTop: 4 }, sessionPause: { flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', minHeight: 44, borderRadius: 6, backgroundColor: '#F2F4F7', marginRight: 10 }, sessionPauseText: { color: NAVY, fontSize: 14, marginLeft: 6 }, sessionStop: { flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', minHeight: 44, borderRadius: 6, backgroundColor: ORANGE }, sessionDone: { color: '#15803D', fontSize: 13, textAlign: 'center', marginTop: 12 }, greenStart: { minHeight: 50, borderRadius: 8, backgroundColor: '#118000', alignItems: 'center', justifyContent: 'center', marginTop: 4, elevation: 3 },
  liveDot: { position: 'absolute', top: -2, right: -2, width: 10, height: 10, borderRadius: 5, backgroundColor: ORANGE }, palmingImage: { alignSelf: 'center', width: 150, height: 120, marginVertical: 14 }, guidanceBox: { backgroundColor: '#FFF8F1', borderRadius: 6, padding: 14, marginTop: 16, marginBottom: 14 }, guidanceTitle: { color: NAVY, fontSize: 13, marginLeft: 6 }, inProgressPill: { minHeight: 44, borderRadius: 6, borderWidth: 1, borderColor: '#FFC9A3', backgroundColor: '#FFEBDA', alignItems: 'center', justifyContent: 'center' }, inProgressText: { color: ORANGE, fontSize: 14 },
  blinkIcon: { width: 76, height: 76, borderRadius: 38, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', marginTop: 6, marginBottom: 16 }, sessionTitle: { color: NAVY, fontSize: 17, fontWeight: '600', marginBottom: 8 }, sessionSubtitle: { color: NAVY, fontSize: 13, lineHeight: 19, textAlign: 'center', marginBottom: 16, paddingHorizontal: 10 }, blinkTimer: { alignSelf: 'stretch', backgroundColor: '#ECECEC', borderRadius: 8, paddingVertical: 14, alignItems: 'center', marginBottom: 16 }, blinkClock: { color: NAVY, fontSize: 26 }, blinkInstruction: { alignSelf: 'stretch', backgroundColor: '#EEF4FF', borderRadius: 8, padding: 14, marginBottom: 16 }, blinkInstructionText: { color: NAVY, fontSize: 12, lineHeight: 18, textAlign: 'center' },
  ruleRingOuter: { width: 190, height: 190, borderRadius: 95, borderWidth: 4, borderColor: '#D0D5DD', alignItems: 'center', justifyContent: 'center', marginBottom: 6 }, ruleRingInner: { width: 156, height: 156, borderRadius: 78, borderWidth: 8, borderColor: ORANGE, alignItems: 'center', justifyContent: 'center' }, ruleRingActive: { borderColor: '#E06A00' }, ruleEyes: { fontSize: 44 }, ruleNextBox: { alignSelf: 'stretch', backgroundColor: '#F7F7F7', borderRadius: 8, alignItems: 'center', paddingVertical: 16, marginBottom: 6 }, ruleNextValue: { color: NAVY, fontSize: 30, fontWeight: '700', marginTop: 2 }, ruleLookBox: { alignSelf: 'stretch', backgroundColor: '#FFF1E6', borderWidth: 1, borderColor: '#FFD2B0', borderRadius: 8, alignItems: 'center', paddingVertical: 18, marginBottom: 16 }, ruleLookValue: { color: ORANGE, fontSize: 36, fontWeight: '700', marginTop: 2 },
  rollRing: { width: 200, height: 200, borderRadius: 100, borderWidth: 6, borderColor: '#F7C9A3', backgroundColor: '#E4E4E4', alignItems: 'center', justifyContent: 'center', marginBottom: 18, elevation: 3 }, rollOrbit: { position: 'absolute', width: 188, height: 188, alignItems: 'center' }, rollDot: { width: 16, height: 16, borderRadius: 8, backgroundColor: ORANGE, marginTop: -2 }, rollInner: { width: 118, height: 118, borderRadius: 59, borderWidth: 2, borderStyle: 'dashed', borderColor: '#F5A66B', alignItems: 'center', justifyContent: 'center' }, rollValue: { color: NAVY, fontSize: 19, fontWeight: '700' }, rollIcon: { position: 'absolute', top: 4, right: 0 },
  rollOptionRow: { alignSelf: 'stretch', flexDirection: 'row', alignItems: 'center', marginBottom: 14 }, rollLabel: { color: '#475467', fontSize: 13, marginRight: 12 }, directionChip: { flexDirection: 'row', alignItems: 'center', borderRadius: 14, paddingHorizontal: 12, paddingVertical: 6, marginRight: 8 }, directionChipActive: { backgroundColor: ORANGE }, directionText: { color: '#475467', fontSize: 12, marginLeft: 5 }, directionTextActive: { color: WHITE }, rollSettingRow: { alignSelf: 'stretch', flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 12 }, rollDuration: { color: ORANGE, fontSize: 16 }, rollEdit: { color: ORANGE, fontSize: 12, textDecorationLine: 'underline' }, repButton: { width: 28, height: 28, borderRadius: 14, backgroundColor: '#FFE1C8', alignItems: 'center', justifyContent: 'center' }, repValue: { color: NAVY, fontSize: 14, marginHorizontal: 14 }, demoLink: { color: '#475467', fontSize: 13, textDecorationLine: 'underline', marginTop: 14 },
  progressOverview: { padding: 16, paddingBottom: 28 }, progressCard: { backgroundColor: WHITE, borderRadius: 10, borderWidth: 1, borderColor: '#F0F0F0', paddingHorizontal: 20, paddingVertical: 22, marginBottom: 14, elevation: 1 }, progressCardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 }, progressCardTitle: { color: NAVY, fontSize: 16, fontWeight: '600' }, progressBody: { color: '#3D3D3D', fontSize: 14, lineHeight: 21 },
  miniChart: { backgroundColor: '#F4F4F4', borderRadius: 4, height: 104, justifyContent: 'flex-end', paddingHorizontal: 10, paddingTop: 12, marginTop: 14 }, miniChartEmpty: { flex: 1, alignItems: 'center', justifyContent: 'center' }, miniChartText: { color: '#667085', fontSize: 12, marginTop: 4 },
  barRow: { flexDirection: 'row', alignItems: 'flex-end' }, barSlot: { flex: 1, alignItems: 'center', justifyContent: 'flex-end', height: '100%' }, bar: { width: '72%', borderTopLeftRadius: 2, borderTopRightRadius: 2 }, barLabels: { flexDirection: 'row', marginTop: 8 }, barLabel: { flex: 1, color: '#475467', fontSize: 11, textAlign: 'center' }, barHours: { flex: 1, color: NAVY, fontSize: 11, fontWeight: '600', textAlign: 'center' },
  timeCircle: { alignSelf: 'center', width: 112, height: 112, borderRadius: 56, backgroundColor: '#FFD9BC', alignItems: 'center', justifyContent: 'center', marginVertical: 6 }, divider: { height: 1, backgroundColor: '#EEEEEE', marginTop: 18, marginBottom: 14 }, weekAverage: { color: NAVY, fontSize: 13, fontWeight: '600' }, overviewHistoryRow: { flexDirection: 'row', alignItems: 'center', paddingVertical: 10 },
  progressHeader: { minHeight: 56, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 14, backgroundColor: WHITE, borderBottomWidth: 1, borderBottomColor: '#EEEEEE' }, progressBack: { width: 40, alignItems: 'center' }, progressHeaderTitle: { color: NAVY, fontSize: 17, fontWeight: '700' },
  progressPage: { flex: 1, backgroundColor: '#F4F4F4' }, progressDetail: { padding: 16, paddingBottom: 28 }, progressPanel: { backgroundColor: WHITE, borderRadius: 10, borderWidth: 1, borderColor: '#ECECEC', padding: 18, marginBottom: 12 }, centered: { alignItems: 'center' }, alignEnd: { alignItems: 'flex-end' }, panelTitle: { color: NAVY, fontSize: 14, fontWeight: '700', marginBottom: 10 }, panelTitleInline: { marginBottom: 0 },
  chartArea: { flexDirection: 'row' }, chartAxis: { width: 22, height: 190, marginRight: 6 }, chartAxisTick: { position: 'absolute', right: 0 }, chartTick: { color: '#667085', fontSize: 10 }, chartGrid: { position: 'absolute', left: 0, right: 0, top: 0, height: 190 }, chartGridLine: { position: 'absolute', left: 0, right: 0, height: 1, backgroundColor: '#EAECF0' }, chartDayLabel: { color: NAVY }, chartCaption: { color: NAVY, fontSize: 12, fontWeight: '600', textAlign: 'center', marginTop: 12 },
  breakTotal: { color: NAVY, fontSize: 22, fontWeight: '700', marginBottom: 8 }, streakBox: { alignSelf: 'stretch', backgroundColor: '#F7F7F7', borderRadius: 6, paddingVertical: 16, alignItems: 'center', marginTop: 16 }, streakText: { color: NAVY, fontSize: 15 },
  statPair: { flexDirection: 'row', justifyContent: 'space-between' }, statTile: { flex: 1, alignItems: 'center', marginHorizontal: 0 }, statTileValue: { color: NAVY, fontSize: 21, fontWeight: '700', marginBottom: 6 }, statTileLabel: { color: '#475467', fontSize: 12 },
  noteRow: { flexDirection: 'row', alignItems: 'flex-start' }, noteIcon: { width: 28, height: 28, borderRadius: 14, backgroundColor: '#E06A00', alignItems: 'center', justifyContent: 'center', marginRight: 12 }, noteTitle: { color: NAVY, fontSize: 14, fontWeight: '700', marginBottom: 6 }, noteText: { color: '#475467', fontSize: 13, lineHeight: 19 },
  usageRing: { alignSelf: 'center', width: 104, height: 104, borderRadius: 52, borderWidth: 6, borderColor: '#FFB57A', alignItems: 'center', justifyContent: 'center', marginVertical: 10 }, usageValue: { color: NAVY, fontSize: 18, fontWeight: '500' }, usageGoal: { color: '#475467', fontSize: 10 }, usageTrack: { height: 6, borderRadius: 3, backgroundColor: '#E5E5E5', overflow: 'hidden', marginTop: 8 }, usageFill: { height: 6, borderRadius: 3, backgroundColor: '#E06A00' }, usageScale: { flexDirection: 'row', justifyContent: 'space-between', marginTop: 6 },
  goalLine: { position: 'absolute', left: 0, right: 0, borderTopWidth: 1, borderStyle: 'dashed', borderColor: '#98A2B3', zIndex: 1 }, goalLabel: { position: 'absolute', right: 0, top: -14, color: '#475467', fontSize: 9, backgroundColor: WHITE },
  averageRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }, averageLabel: { color: '#475467', fontSize: 12, marginBottom: 4 }, averageValue: { color: NAVY, fontSize: 19, fontWeight: '500' }, changeValue: { color: NAVY, fontSize: 15, fontWeight: '600' },
  historyRow: { flexDirection: 'row', alignItems: 'center', paddingVertical: 14, marginBottom: 10 }, historyDate: { color: NAVY, fontSize: 14 }, historyLevel: { color: '#475467', fontSize: 11, marginTop: 3 }, severityDot: { width: 10, height: 10, borderRadius: 5, marginRight: 14 }, legendRow: { flexDirection: 'row', alignItems: 'center', marginTop: 8 },
  achievementCount: { color: NAVY, fontSize: 12, fontWeight: '600', backgroundColor: '#F2F4F7', borderRadius: 10, paddingHorizontal: 8, paddingVertical: 3, overflow: 'hidden' }, achievementHint: { color: '#475467', fontSize: 11, marginTop: 6 },
  badgeSection: { color: NAVY, fontSize: 14, fontWeight: '700', marginTop: 8, marginBottom: 10 }, badgeGrid: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between' }, badgeCard: { width: '48%', backgroundColor: WHITE, borderRadius: 8, borderWidth: 1, borderColor: '#ECECEC', alignItems: 'center', paddingHorizontal: 10, paddingTop: 16, paddingBottom: 12, marginBottom: 10, elevation: 2 }, badgeLocked: { backgroundColor: '#F7F7F7', elevation: 0 }, badgeIcon: { width: 40, height: 40, borderRadius: 20, alignItems: 'center', justifyContent: 'center', marginBottom: 10 }, badgeTitle: { color: NAVY, fontSize: 13, textAlign: 'center', marginBottom: 4 }, badgeTitleLocked: { color: '#98A2B3' }, badgeDate: { color: '#475467', fontSize: 10, textAlign: 'center' }, badgeUnderline: { width: 26, height: 2, borderRadius: 1, backgroundColor: '#C2410C', marginTop: 8 },
  resultPage: { flex: 1, backgroundColor: '#F4F4F4' }, bold: { fontWeight: '700' },
  resultSummary: { alignItems: 'center', paddingHorizontal: 28, paddingTop: 36, paddingBottom: 24 }, resultMascot: { width: 190, height: 190, marginBottom: 26 }, resultHeadline: { color: NAVY, fontSize: 22, textAlign: 'center', marginBottom: 18 }, resultMessage: { color: '#3D3D3D', fontSize: 16, lineHeight: 23, textAlign: 'center', marginBottom: 32, paddingHorizontal: 8 },
  resultPrimary: { alignSelf: 'stretch', marginHorizontal: 12, minHeight: 50, borderRadius: 6, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', marginBottom: 18, elevation: 3 }, resultPrimaryText: { color: WHITE, fontSize: 16, fontWeight: '700' }, resultSecondary: { alignSelf: 'stretch', marginHorizontal: 12, minHeight: 50, borderRadius: 6, backgroundColor: NAVY, alignItems: 'center', justifyContent: 'center', elevation: 3 }, resultSecondaryText: { color: WHITE, fontSize: 16, fontWeight: '600' },
  resultFooter: { backgroundColor: WHITE, borderTopWidth: 1, borderTopColor: '#EEEEEE', paddingHorizontal: 24, paddingVertical: 20 }, resultFooterRow: { flexDirection: 'row', justifyContent: 'center' }, resultRetake: { flex: 1, minHeight: 44, borderRadius: 8, borderWidth: 1, borderColor: '#D5D5D5', backgroundColor: '#F2F2F2', alignItems: 'center', justifyContent: 'center', marginRight: 12 }, resultRetakeText: { color: NAVY, fontSize: 13 }, resultHome: { flex: 1, minHeight: 44, borderRadius: 8, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', elevation: 3 }, resultHomeNavy: { backgroundColor: NAVY }, resultHomeText: { color: WHITE, fontSize: 14, fontWeight: '600' },
  resultTopCard: { backgroundColor: WHITE, borderRadius: 4, borderWidth: 1, borderColor: '#EEEEEE', alignItems: 'center', paddingHorizontal: 18, paddingVertical: 20, marginBottom: 8 }, resultIconCircle: { width: 50, height: 50, borderRadius: 25, backgroundColor: '#F2F2F2', alignItems: 'center', justifyContent: 'center', marginBottom: 14 },
  breakdownContent: { flexGrow: 1 }, breakdownTop: { backgroundColor: '#F4F4F4', padding: 16, paddingBottom: 8 }, breakdownTitle: { color: NAVY, fontSize: 18, fontWeight: '600', marginBottom: 18 }, breakdownLevelBox: { alignSelf: 'stretch', backgroundColor: '#F7F7F7', borderWidth: 1, borderColor: '#E5E5E5', borderRadius: 4, alignItems: 'center', paddingVertical: 16 }, breakdownLevel: { fontSize: 17, fontWeight: '600', marginBottom: 6 }, breakdownScoreLine: { color: '#3D3D3D', fontSize: 13 },
  breakdownBody: { flexGrow: 1, backgroundColor: WHITE, paddingHorizontal: 20, paddingVertical: 18 }, breakdownHeading: { color: NAVY, fontSize: 15, fontWeight: '600', textAlign: 'center', marginBottom: 16 }, breakdownCard: { backgroundColor: '#F8F8F8', borderWidth: 1, borderColor: '#E5E5E5', borderRadius: 4, padding: 14, marginBottom: 14 }, breakdownCardTitle: { color: NAVY, fontSize: 14, fontWeight: '500', marginBottom: 10 }, breakdownSmall: { color: '#3D3D3D', fontSize: 12 }, breakdownRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 14 }, breakdownOption: { fontSize: 15, fontWeight: '700' }, breakdownTrack: { height: 8, borderRadius: 4, backgroundColor: '#E5E5E5', marginTop: 10, overflow: 'hidden' }, breakdownFill: { height: 8, borderRadius: 4 }, breakdownTick: { color: '#777777', fontSize: 10 }, breakdownTipsButton: { alignSelf: 'center', width: '80%', minHeight: 50, borderRadius: 6, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', marginTop: 14, marginBottom: 14, elevation: 3 },
  tipsContent: { padding: 16, paddingBottom: 28 }, tipsUrgentCircle: { backgroundColor: NAVY }, tipsTitle: { color: NAVY, fontSize: 21, fontWeight: '600', textAlign: 'center', marginBottom: 12 }, tipsIntro: { color: '#3D3D3D', fontSize: 14, lineHeight: 21, textAlign: 'center' },
  tipCard: { flexDirection: 'row', backgroundColor: WHITE, borderRadius: 8, borderWidth: 1, borderColor: '#EEEEEE', padding: 16, marginBottom: 8 }, tipIcon: { width: 34, height: 34, borderRadius: 17, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', marginRight: 14 }, tipTitle: { color: NAVY, fontSize: 15, fontWeight: '600', lineHeight: 22, marginBottom: 8 }, tipText: { color: '#555555', fontSize: 12, lineHeight: 19 },
  tipNote: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F8F8F8', borderWidth: 1, borderColor: '#E5E5E5', borderRadius: 4, padding: 12, marginTop: 4, marginBottom: 8 }, tipNoteText: { flex: 1, color: '#3D3D3D', fontSize: 12, lineHeight: 18, marginLeft: 10 }, tipsBack: { minHeight: 50, borderRadius: 6, backgroundColor: NAVY, alignItems: 'center', justifyContent: 'center', marginTop: 16 },
  studentIntro: { flex: 1, paddingHorizontal: 28, paddingTop: 36, paddingBottom: 28, alignItems: 'center', backgroundColor: '#F4F4F4' }, studentIntroTitle: { color: NAVY, fontSize: 26, fontWeight: '700', textAlign: 'center', marginBottom: 24 }, studentIntroText: { color: '#3D3D3D', fontSize: 16, lineHeight: 23, textAlign: 'center' }, studentIntroImage: { flex: 1, width: '100%', maxHeight: 340, marginVertical: 20 }, studentProceed: { alignSelf: 'stretch', marginTop: 'auto', minHeight: 52, borderRadius: 8, backgroundColor: NAVY, alignItems: 'center', justifyContent: 'center' },
  studentGuide: { flexGrow: 1, padding: 22, alignItems: 'center', backgroundColor: '#F4F4F4' }, studentGuideImage: { width: 230, height: 180, marginBottom: 18 }, studentGuideCard: { alignSelf: 'stretch', backgroundColor: WHITE, borderRadius: 8, paddingHorizontal: 18, paddingVertical: 22, elevation: 3 }, studentGuideText: { color: '#3D3D3D', fontSize: 14, lineHeight: 21, textAlign: 'center' }, studentGuideRow: { borderLeftWidth: 3, borderLeftColor: ORANGE, paddingLeft: 16, paddingVertical: 8, marginTop: 14 }, studentGuideLabel: { color: NAVY, fontSize: 14, fontWeight: '700', marginBottom: 4 }, studentGuideDescription: { color: '#3D3D3D', fontSize: 12, lineHeight: 18 }, studentContinue: { minHeight: 54, minWidth: 250, marginTop: 28, borderRadius: 8, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 24, elevation: 3 }, studentContinueText: { color: WHITE, fontSize: 18, fontWeight: '700' },
  studentQuestionTop: { backgroundColor: WHITE, paddingHorizontal: 34, paddingTop: 16, paddingBottom: 18, alignItems: 'center' }, studentProgressLabels: { alignSelf: 'stretch', flexDirection: 'row', justifyContent: 'space-between' }, studentProgressText: { color: '#3D3D3D', fontSize: 12 }, studentProgressTrack: { alignSelf: 'stretch', height: 6, borderRadius: 3, backgroundColor: '#E5E5E5', marginTop: 8, overflow: 'hidden' }, studentProgressFill: { height: 6, borderRadius: 3, backgroundColor: ORANGE }, studentQuestionImage: { width: 140, height: 140, marginTop: 14 },
  studentQuestionBody: { flex: 1, backgroundColor: '#F4F4F4' }, studentQuestionContent: { paddingHorizontal: 28, paddingVertical: 20 }, studentQuestionCard: { backgroundColor: WHITE, borderRadius: 10, paddingHorizontal: 20, paddingVertical: 26, marginBottom: 26 }, studentQuestionText: { color: NAVY, fontSize: 17, lineHeight: 26, fontWeight: '700', textAlign: 'center' }, studentOption: { flexDirection: 'row', alignItems: 'center', minHeight: 56, backgroundColor: WHITE, borderRadius: 8, borderWidth: 1.5, borderColor: '#E1E1E1', paddingHorizontal: 16, marginBottom: 12 }, studentOptionSelected: { borderColor: ORANGE }, studentOptionText: { color: NAVY, fontSize: 15 }, studentRadio: { width: 18, height: 18, borderRadius: 9, borderWidth: 1.5, borderColor: '#B5B5B5', alignItems: 'center', justifyContent: 'center', marginRight: 12 }, studentRadioSelected: { borderColor: ORANGE }, studentRadioDot: { width: 9, height: 9, borderRadius: 5, backgroundColor: ORANGE },
  studentFooter: { flexDirection: 'row', justifyContent: 'center', backgroundColor: WHITE, borderTopWidth: 1, borderTopColor: '#EEEEEE', paddingHorizontal: 28, paddingVertical: 18 }, studentBack: { flex: 1, minHeight: 46, borderRadius: 8, borderWidth: 1, borderColor: '#D5D5D5', backgroundColor: '#F2F2F2', alignItems: 'center', justifyContent: 'center', marginRight: 12 }, studentBackText: { color: NAVY, fontSize: 15 }, studentNext: { flex: 1, minHeight: 46, borderRadius: 8, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', elevation: 3 },
  profileCameraIcon: { width: 16, height: 14, alignItems: 'center', justifyContent: 'flex-end' }, profileCameraTop: { position: 'absolute', top: 0, left: 3, width: 6, height: 3, borderTopLeftRadius: 2, borderTopRightRadius: 2, backgroundColor: WHITE }, profileCameraBody: { width: 15, height: 10, borderRadius: 2, backgroundColor: WHITE, alignItems: 'center', justifyContent: 'center' }, profileCameraLens: { width: 5, height: 5, borderRadius: 3, backgroundColor: NAVY }, gearShape: { width: 22, height: 22, alignItems: 'center', justifyContent: 'center' }, gearBar: { position: 'absolute', width: 22, height: 6, borderRadius: 2, backgroundColor: ORANGE }, gearCore: { width: 14, height: 14, borderRadius: 7, alignItems: 'center', justifyContent: 'center', backgroundColor: ORANGE }, gearHole: { width: 6, height: 6, borderRadius: 3, backgroundColor: WHITE },
  assessmentPromptBackdrop: { flex: 1, backgroundColor: '#0006', alignItems: 'center', justifyContent: 'center', padding: 24 }, assessmentPrompt: { width: '74%', maxWidth: 300, backgroundColor: WHITE, borderRadius: 8, padding: 12, elevation: 12 }, assessmentPromptTitle: { color: NAVY, fontSize: 13, fontWeight: '700', marginBottom: 8 }, assessmentPromptMessage: { color: '#555555', fontSize: 11, lineHeight: 15 }, assessmentPromptActions: { flexDirection: 'row', gap: 6, marginTop: 14 }, assessmentPromptCancel: { flex: 1, minHeight: 30, backgroundColor: '#F4F4F4', borderRadius: 9, borderWidth: 1, borderColor: '#D8D8D8', alignItems: 'center', justifyContent: 'center' }, assessmentPromptCancelText: { color: NAVY, fontSize: 10 }, assessmentPromptNext: { flex: 1, minHeight: 30, backgroundColor: ORANGE, borderRadius: 9, alignItems: 'center', justifyContent: 'center' }, assessmentPromptNextText: { color: WHITE, fontSize: 10, fontWeight: '700' },
  profilePage: { paddingHorizontal: 14, paddingTop: 12, paddingBottom: 28 }, profileHeader: { height: 52, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: WHITE, paddingHorizontal: 12 }, profileHeaderAction: { width: 42, height: 48, alignItems: 'center', justifyContent: 'center' }, profileHeaderBack: { color: '#344054', fontSize: 34, lineHeight: 40 }, profileHeaderTitle: { color: NAVY, fontSize: 15, fontWeight: '700' }, profileMenu: { color: NAVY, fontSize: 24 }, profileSummaryCard: { padding: 12 }, profileSummary: { alignItems: 'center' }, profileAvatarWrap: { width: 82, height: 82, marginBottom: 5 }, profileAvatar: { width: 76, height: 76, borderRadius: 38, borderWidth: 3, borderColor: NAVY, backgroundColor: '#DDE7EA', alignItems: 'center', justifyContent: 'center' }, profileAvatarText: { color: NAVY, fontSize: 24, fontWeight: '700' }, profileCamera: { position: 'absolute', right: 0, bottom: 0, width: 27, height: 27, borderRadius: 14, backgroundColor: NAVY, borderWidth: 2, borderColor: WHITE, alignItems: 'center', justifyContent: 'center' }, profileCameraText: { color: WHITE, fontSize: 14 }, profileName: { color: NAVY, fontSize: 16, fontWeight: '700', marginTop: 2 }, profileEmail: { color: '#87909C', fontSize: 11, marginTop: 2, maxWidth: '95%' }, profileStats: { flexDirection: 'row', justifyContent: 'space-around', width: '100%', marginTop: 14, marginBottom: 14 }, profileStat: { flex: 1, alignItems: 'center' }, profileStatIconBox: { width: 69, height: 40, borderRadius: 10, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', marginBottom: 5 }, profileStatEye: { width: 18, height: 12, borderWidth: 2, borderColor: WHITE, borderRadius: 10, alignItems: 'center', justifyContent: 'center' }, profileStatEyeIris: { width: 7, height: 7, borderRadius: 4, backgroundColor: WHITE, alignItems: 'center', justifyContent: 'center' }, profileStatEyePupil: { width: 3, height: 3, borderRadius: 2, backgroundColor: ORANGE }, profileStatClock: { width: 16, height: 16, borderRadius: 8, backgroundColor: WHITE }, profileStatClockHandLong: { position: 'absolute', width: 2, height: 5, top: 3, left: 7, borderRadius: 1, backgroundColor: ORANGE }, profileStatClockHandShort: { position: 'absolute', width: 4, height: 2, top: 7, left: 7, borderRadius: 1, backgroundColor: ORANGE }, profileStatTrophy: { width: 20, height: 20, alignItems: 'center', justifyContent: 'flex-start' }, profileStatTrophyCup: { width: 12, height: 9, backgroundColor: WHITE, borderBottomLeftRadius: 5, borderBottomRightRadius: 5 }, profileStatTrophyHandle: { position: 'absolute', top: 1, width: 5, height: 6, borderWidth: 2, borderColor: WHITE }, profileStatTrophyHandleLeft: { left: 0, borderRightWidth: 0, borderTopLeftRadius: 4, borderBottomLeftRadius: 4 }, profileStatTrophyHandleRight: { right: 0, borderLeftWidth: 0, borderTopRightRadius: 4, borderBottomRightRadius: 4 }, profileStatTrophyStem: { width: 3, height: 4, backgroundColor: WHITE }, profileStatTrophyBase: { width: 12, height: 2, borderRadius: 2, backgroundColor: WHITE }, profileStatLabel: { color: NAVY, fontSize: 10, fontWeight: '600' }, profileStatValue: { color: '#7E8792', fontSize: 9, marginTop: 2 }, editProfileButton: { minHeight: 36, width: '100%', borderRadius: 7, backgroundColor: NAVY, alignItems: 'center', justifyContent: 'center' }, editProfileText: { color: WHITE, fontSize: 11, fontWeight: '600' }, profileInfoCard: { padding: 15 }, profileSectionHeader: { flexDirection: 'row', flexWrap: 'wrap', alignItems: 'center', justifyContent: 'space-between', columnGap: 8, rowGap: 4, marginBottom: 8 }, profileSectionTitle: { color: NAVY, fontSize: 14, fontWeight: '700' }, profileEditIcon: { color: ORANGE, fontSize: 20, fontWeight: '700' }, profileInfoRow: { minHeight: 46, flexDirection: 'row', alignItems: 'center', marginTop: 4 }, profileInfoIconBox: { width: 27, height: 30, borderRadius: 6, backgroundColor: '#F6F7F9', alignItems: 'center', justifyContent: 'center', marginRight: 9 }, profileInfoIcon: { color: '#475467', fontSize: 14 }, profileInfoText: { flex: 1 }, profileInfoLabel: { color: '#77808B', fontSize: 10 }, profileInfoValue: { color: NAVY, fontSize: 12, marginTop: 2 }, profileMoreLink: { color: ORANGE, fontSize: 11 }, latestAssessmentPanel: { backgroundColor: '#FAFAFA', borderRadius: 8, padding: 12, marginTop: 12, marginBottom: 10 }, latestAssessmentHeading: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, latestAssessmentTitle: { color: NAVY, fontSize: 14, fontWeight: '700', flex: 1 }, latestAssessmentDot: { width: 9, height: 9, borderRadius: 5, marginLeft: 8 }, latestAssessmentDate: { color: '#667085', fontSize: 11, marginTop: 7 }, latestAssessmentSummary: { color: '#555555', fontSize: 11, lineHeight: 16, marginTop: 9 }, latestAssessmentButton: { minHeight: 42, borderRadius: 7, backgroundColor: NAVY, alignItems: 'center', justifyContent: 'center', marginTop: 2 }, latestAssessmentButtonText: { color: WHITE, fontSize: 12, fontWeight: '700' }, profileReminderRow: { flexDirection: 'row', alignItems: 'center', paddingTop: 5 }, profileReminderIcon: { width: 24, height: 28, borderRadius: 5, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', marginRight: 9 }, profileReminderIconText: { color: WHITE, fontSize: 16 }, profileModalBackdrop: { flex: 1, backgroundColor: '#0008', justifyContent: 'flex-end' }, profileModal: { backgroundColor: WHITE, padding: 20, borderTopLeftRadius: 20, borderTopRightRadius: 20, maxHeight: '90%' }, profileEditField: { marginBottom: 10 }, profileEditInput: { minHeight: 42, borderRadius: 8, borderWidth: 1, borderColor: '#D0D5DD', paddingHorizontal: 10, marginTop: 4, color: NAVY },
  safe: { flex: 1, backgroundColor: WARM }, fill: { flex: 1 }, center: { flex: 1, alignItems: 'center', justifyContent: 'center', backgroundColor: WARM },
  legalPage: { flex: 1, backgroundColor: WARM }, legalHeader: { height: 52, flexDirection: 'row', alignItems: 'center', backgroundColor: WHITE, paddingHorizontal: 12 }, legalBack: { width: 40, height: 48, justifyContent: 'center' }, legalBackIcon: { color: '#475467', fontSize: 34, lineHeight: 40 }, legalBackLabel: { color: '#1D2939', fontSize: 14, fontWeight: '500' }, legalTabs: { height: 48, flexDirection: 'row', backgroundColor: '#F1EEEE', borderBottomWidth: 1, borderBottomColor: '#D8D4D2' }, legalTab: { flex: 1, alignItems: 'center', justifyContent: 'center', borderBottomWidth: 2, borderBottomColor: 'transparent' }, legalTabSelected: { borderBottomColor: ORANGE }, legalTabText: { color: '#777777', fontSize: 13 }, legalTabTextSelected: { color: NAVY }, legalScroll: { flex: 1 }, legalDocument: { paddingHorizontal: 16, paddingTop: 20, paddingBottom: 24 }, legalTitle: { color: NAVY, fontSize: 16, fontWeight: '700', marginBottom: 12 }, legalSectionTitle: { color: NAVY, fontSize: 14, fontWeight: '500', marginTop: 18, marginBottom: 8 }, legalParagraph: { color: '#666666', fontSize: 11, lineHeight: 18 }, legalFooter: { backgroundColor: WHITE, paddingHorizontal: 28, paddingTop: 12, paddingBottom: 14 },
  notificationHeader: { height: 52, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: WHITE, paddingHorizontal: 12 }, notificationBack: { width: 42, height: 48, justifyContent: 'center' }, notificationBackIcon: { color: '#475467', fontSize: 34, lineHeight: 40 }, notificationHeaderTitle: { color: NAVY, fontSize: 15, fontWeight: '700' }, notificationFilter: { width: 42, height: 48, alignItems: 'center', justifyContent: 'center' }, notificationFilterIcon: { color: '#777777', fontSize: 23 }, notificationList: { paddingHorizontal: 16, paddingTop: 8, paddingBottom: 24 }, notificationDate: { color: '#777777', fontSize: 11, textAlign: 'center', paddingVertical: 10 }, notificationCard: { flexDirection: 'row', alignItems: 'flex-start', backgroundColor: WHITE, borderRadius: 14, borderWidth: 1, borderColor: '#E5E7EA', padding: 12, marginVertical: 5, elevation: 1 }, notificationIconCircle: { width: 30, height: 30, borderRadius: 15, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', marginRight: 9 }, notificationIcon: { color: WHITE, fontSize: 17, fontWeight: '700' }, notificationContent: { flex: 1 }, notificationTitle: { color: NAVY, fontSize: 12, fontWeight: '700', marginTop: 2 }, notificationMessage: { color: '#555555', fontSize: 11, lineHeight: 17, marginTop: 9 }, notificationAction: { alignSelf: 'flex-end', paddingTop: 8, paddingHorizontal: 4 }, notificationActionText: { color: ORANGE, fontSize: 10, fontWeight: '500' }, notificationEmpty: { color: '#777777', fontSize: 11, textAlign: 'center', paddingVertical: 10 },
  welcomePage: { flex: 1, padding: 24, alignItems: 'center', justifyContent: 'space-between' }, welcomeBrand: { flex: 1, alignItems: 'center', justifyContent: 'center' }, welcomeImage: { width: 240, height: 170 },
  tagline: { color: '#777777', fontSize: 12, letterSpacing: 0.4, textAlign: 'center', marginTop: 8 }, authPage: { padding: 20, paddingBottom: 32, alignItems: 'stretch' }, authLogo: { alignSelf: 'center', width: 170, height: 58, marginTop: 6 }, authSubtitle: { color: '#526174', fontSize: 14, textAlign: 'center', marginVertical: 22 }, authCard: { backgroundColor: WHITE, borderRadius: 20, padding: 20, elevation: 3 }, field: { marginBottom: 14, position: 'relative' }, fieldLabel: { color: NAVY, fontWeight: '600', fontSize: 12, marginBottom: 7 }, input: { backgroundColor: WHITE, minHeight: 46, borderWidth: 1, borderColor: '#D6DDE4', borderRadius: 10, paddingHorizontal: 13, paddingVertical: 10, color: NAVY }, showPassword: { position: 'absolute', right: 12, bottom: 13, color: ORANGE, fontSize: 12 },
  header: { minHeight: 54, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 14, backgroundColor: WHITE }, headerTitle: { color: NAVY, fontSize: 17, fontWeight: '600' }, back: { width: 42, alignItems: 'center' }, backText: { fontSize: 36, lineHeight: 40, color: NAVY }, homeHeader: { height: 54, backgroundColor: WHITE, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 16 }, brandText: { color: NAVY, fontSize: 21, fontWeight: '700' }, homeLogo: { width: 116, height: 42 }, headerAction: { width: 48, height: 48, alignItems: 'center', justifyContent: 'center' }, headerIcon: { color: ORANGE, fontSize: 24 }, avatarButton: { width: 44, height: 44, alignItems: 'center', justifyContent: 'center' }, avatar: { overflow: 'hidden', backgroundColor: NAVY, color: WHITE, fontSize: 14, fontWeight: '700', textAlign: 'center', textAlignVertical: 'center', width: 30, height: 30, borderRadius: 15 },
  page: { padding: 20, paddingBottom: 28 }, title: { color: NAVY, fontSize: 22, fontWeight: '700', textAlign: 'center', marginBottom: 8 }, muted: { color: '#666666', fontSize: 12, lineHeight: 18, marginBottom: 12 }, bodyText: { color: NAVY, fontSize: 14, lineHeight: 21 }, card: { backgroundColor: WHITE, borderRadius: 15, padding: 16, marginVertical: 7, elevation: 1 }, cardTitle: { color: NAVY, fontSize: 15, fontWeight: '700', marginBottom: 6 }, button: { minHeight: 48, backgroundColor: ORANGE, borderRadius: 12, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 16, marginVertical: 7, elevation: 2 }, buttonText: { color: WHITE, fontWeight: '700', fontSize: 15, textAlign: 'center' }, buttonSecondary: { backgroundColor: NAVY }, buttonSecondaryText: { color: WHITE }, disabled: { opacity: 0.55 }, link: { color: ORANGE, fontWeight: '600', fontSize: 12 }, linkCenter: { color: '#777777', fontSize: 13, textAlign: 'center', padding: 12, textDecorationLine: 'underline' },
  roleCard: { backgroundColor: WHITE, borderRadius: 12, borderWidth: 1, borderColor: '#E2E2E2', marginVertical: 9, padding: 12, alignItems: 'center' }, roleSelected: { borderColor: ORANGE, backgroundColor: '#FFF8F1' }, roleImage: { width: 142, height: 144 }, roleText: { color: WHITE, backgroundColor: NAVY, overflow: 'hidden', borderRadius: 7, textAlign: 'center', width: '100%', padding: 12, fontSize: 16 }, languageImage: { width: 200, height: 170, alignSelf: 'center', marginVertical: 6 }, languageChoice: { backgroundColor: WHITE, borderRadius: 9, padding: 14, marginVertical: 5, flexDirection: 'row', justifyContent: 'space-between', borderWidth: 1, borderColor: '#DDDDDD' }, languageSelected: { borderColor: ORANGE, backgroundColor: '#FFF8F1' }, languagePill: { borderWidth: 1, borderColor: '#D9DEE4', borderRadius: 18, paddingHorizontal: 12, paddingVertical: 8, margin: 4 },
  homePage: { paddingHorizontal: 18, paddingTop: 14, paddingBottom: 24 }, greeting: { flexDirection: 'row', alignItems: 'center', minHeight: 96 }, greetingCard: { padding: 16, minHeight: 108, marginBottom: 10 }, greetingTitle: { color: NAVY, fontSize: 18, fontWeight: '700', marginBottom: 8 }, flex: { flex: 1 }, greetingImage: { width: 112, height: 104 }, featureCard: { minHeight: 110, flexDirection: 'row', alignItems: 'center', backgroundColor: NAVY, borderRadius: 13, paddingHorizontal: 18, paddingVertical: 16, marginVertical: 6, elevation: 3 }, featureIconCircle: { width: 38, height: 38, borderRadius: 19, backgroundColor: WHITE, alignItems: 'center', justifyContent: 'center', marginRight: 12 }, featureCopy: { flex: 1 }, featureTitle: { color: WHITE, fontSize: 15, lineHeight: 19, fontWeight: '700', marginBottom: 5 }, featureSubtitle: { color: '#D7E0E6', fontSize: 11, lineHeight: 16 }, featureArrow: { color: WHITE, fontSize: 30, paddingLeft: 8 }, clipboardIcon: { width: 15, height: 18, backgroundColor: ORANGE, borderRadius: 2, alignItems: 'center', paddingTop: 3 }, clipboardClip: { position: 'absolute', top: -2, width: 8, height: 4, borderRadius: 2, borderWidth: 1, borderColor: ORANGE, backgroundColor: WHITE }, clipboardCheck: { color: WHITE, fontSize: 8, lineHeight: 8, fontWeight: '700' }, clipboardLine: { width: 8, height: 1, backgroundColor: WHITE, marginTop: 2 }, heartIcon: { color: ORANGE, fontSize: 23, lineHeight: 27 }, clockIcon: { width: 19, height: 19, borderRadius: 10, backgroundColor: ORANGE, position: 'relative' }, clockHandLong: { position: 'absolute', width: 2, height: 6, top: 4, left: 8, backgroundColor: WHITE, borderRadius: 1 }, clockHandShort: { position: 'absolute', width: 5, height: 2, top: 9, left: 9, backgroundColor: WHITE, borderRadius: 1 }, settingsIcon: { color: ORANGE, fontSize: 22, lineHeight: 26 }, statRow: { flexDirection: 'row', justifyContent: 'space-around', paddingTop: 10 }, stat: { color: NAVY, fontSize: 22, textAlign: 'center', lineHeight: 27 }, smallText: { color: '#666666', fontSize: 11 }, tipRow: { flexDirection: 'row', alignItems: 'flex-start' }, tipIconCircle: { width: 32, height: 32, borderRadius: 16, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', marginRight: 10 }, eyeTipGlyph: { width: 16, height: 20, alignItems: 'center', justifyContent: 'center' }, eyeTipBulb: { width: 13, height: 13, borderRadius: 7, backgroundColor: WHITE, alignItems: 'center', justifyContent: 'center' }, eyeTipHighlight: { width: 3, height: 3, borderRadius: 2, backgroundColor: ORANGE, position: 'absolute', top: 2, left: 3 }, eyeTipBulbNeck: { width: 7, height: 2, backgroundColor: WHITE, marginTop: -1 }, eyeTipBulbBase: { width: 6, height: 2, borderRadius: 1, backgroundColor: WHITE, marginTop: 1 }, activityRow: { flexDirection: 'row', alignItems: 'center', paddingTop: 10 }, activityIconCircle: { width: 26, height: 26, borderRadius: 13, backgroundColor: ORANGE, alignItems: 'center', justifyContent: 'center', marginRight: 8 }, activityIcon: { color: WHITE, fontSize: 15, fontWeight: '700' }, activityCopy: { flex: 1 }, activityTitle: { color: NAVY, fontSize: 12, marginBottom: 2 }, retakeButton: { backgroundColor: NAVY, borderRadius: 6, paddingVertical: 5, paddingHorizontal: 10 }, retakeText: { color: WHITE, fontSize: 10, fontWeight: '600' }, recommendation: { color: NAVY, paddingVertical: 6, fontSize: 14 }, resultEye: { color: ORANGE, fontSize: 56, textAlign: 'center' }, resultRisk: { fontSize: 35, fontWeight: '800', textAlign: 'center' },
  bottomNav: { height: 62, backgroundColor: WHITE, flexDirection: 'row', borderTopWidth: 1, borderTopColor: '#E8E6E4', justifyContent: 'space-around', alignItems: 'center' }, navItem: { flex: 1, alignItems: 'center' }, navIcon: { fontSize: 18, color: '#9A9A9A' }, navLabel: { fontSize: 10, color: '#9A9A9A', marginTop: 3 }, navSelected: { color: ORANGE, fontWeight: '700' }, switchRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 10 }, inlineRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' }, checkbox: { color: NAVY, fontSize: 18, marginRight: 7 }, separator: { color: '#7A8492', fontSize: 11, textAlign: 'center', marginVertical: 16 }, socialButton: { minHeight: 42, borderRadius: 10, borderWidth: 1, borderColor: '#E0E3E8', alignItems: 'center', justifyContent: 'center', marginTop: 9 }, bottomLink: { alignItems: 'center', marginTop: 18 }, tourImage: { width: '100%', height: 260, marginVertical: 10 }, wrapRow: { flexDirection: 'row', flexWrap: 'wrap', marginTop: 10 },
};

// Display theme. The style definitions above are the light design; Dark Mode, High Contrast and the
// Text Contrast slider are derived from them by remapping colours, so every screen follows automatically.
const parseColor = (value) => {
  if (typeof value !== 'string' || value[0] !== '#') return null;
  let hex = value.slice(1);
  if (hex.length === 3) hex = hex.split('').map((digit) => digit + digit).join('');
  return hex.length === 6 ? [0, 2, 4].map((index) => parseInt(hex.slice(index, index + 2), 16)) : null;
};
const toHex = (rgb) => `#${rgb.map((value) => Math.round(Math.max(0, Math.min(255, value))).toString(16).padStart(2, '0')).join('')}`;
const mix = (from, to, amount) => from.map((value, index) => value + (to[index] - value) * amount);
const lightness = ([r, g, b]) => (0.2126 * r + 0.7152 * g + 0.0722 * b) / 255;
const saturation = (rgb) => (Math.max(...rgb) - Math.min(...rgb)) / 255;
const DARK_PAGE = [43, 43, 43];
const DARK_CARD = [58, 58, 58];
const DARK_RAISED = [74, 74, 74];
const DARK_ACCENT_SURFACE = [69, 69, 69];
const LIGHT_TEXT = [242, 244, 247];
const MUTED_LIGHT_TEXT = [196, 200, 206];
function themeColor(value, role, theme) {
  const rgb = parseColor(value);
  if (!rgb || (!theme.dark && !theme.highContrast && theme.textContrast === 85)) return value;
  const light = lightness(rgb);
  const accent = saturation(rgb) > 0.35 && light > 0.2;
  let out = rgb;
  if (role === 'text') {
    if (accent) return value;
    if (theme.dark && light < 0.6) out = light < 0.2 ? LIGHT_TEXT : MUTED_LIGHT_TEXT;
    const strong = theme.dark ? [255, 255, 255] : [11, 27, 38];
    const surface = theme.dark ? DARK_CARD : [255, 255, 255];
    const isBodyText = theme.dark ? lightness(out) > 0.6 : lightness(out) < 0.6;
    if (isBodyText && theme.highContrast) out = strong;
    if (isBodyText && theme.textContrast > 85) out = mix(out, strong, ((theme.textContrast - 85) / 15) * 0.8);
    if (isBodyText && theme.textContrast < 85) out = mix(out, surface, ((85 - theme.textContrast) / 35) * 0.55);
    return toHex(out);
  }
  if (role === 'background') {
    if (!theme.dark) return value;
    const tinted = saturation(rgb) >= 0.08;
    if (light > 0.97 && !tinted) out = DARK_CARD;
    else if (light > 0.86 && !tinted) out = DARK_PAGE;
    else if (light > 0.6 && !tinted) out = DARK_RAISED;
    else if (light > 0.75) out = mix(rgb, DARK_CARD, 0.82);
    else if (light < 0.25) out = DARK_ACCENT_SURFACE;
    return toHex(out);
  }
  if (role === 'border') {
    if (theme.dark) {
      if (light > 0.6) out = [80, 80, 80];
      else if (light < 0.25) out = [110, 110, 110];
    } else if (theme.highContrast && light > 0.8 && !accent) out = [152, 162, 179];
    return toHex(out);
  }
  return value;
}
const COLOR_ROLES = { color: 'text', tintColor: 'text', backgroundColor: 'background', borderColor: 'border', borderTopColor: 'border', borderBottomColor: 'border', borderLeftColor: 'border', borderRightColor: 'border' };
const themedDefs = (defs, theme) => Object.fromEntries(Object.entries(defs).map(([name, style]) => [name, Object.fromEntries(
  Object.entries(style).map(([key, value]) => [key, COLOR_ROLES[key] ? themeColor(value, COLOR_ROLES[key], theme) : value]),
)]).map(([name, style]) => [name, theme.monochrome && name.endsWith('Backdrop') ? { ...style, filter: [{ grayscale: 1 }] } : style]));
// Style objects that are already themed, so the Text wrapper doesn't convert them twice.
const themedStyles = new WeakSet();
const styleCache = {};
let currentTheme = { dark: false, highContrast: false, textContrast: 85, monochrome: false };
let styles = null;
function applyTheme(theme) {
  const key = `${theme.dark}-${theme.highContrast}-${theme.textContrast}-${theme.monochrome}`;
  currentTheme = theme;
  if (!styleCache[key]) {
    styleCache[key] = StyleSheet.create(themedDefs(styleDefs, theme));
    Object.values(styleCache[key]).forEach((style) => themedStyles.add(style));
  }
  styles = styleCache[key];
}
applyTheme(currentTheme);
// The MataKo wordmark is navy; dark mode uses a copy with white lettering.
const logoSource = () => (currentTheme.dark ? require('./assets/images/atam-dark.png') : require('./assets/images/atam.png'));
// Theme a colour used outside the shared styles (icons, switches, inline styles).
const paint = (value, role = 'text') => themeColor(value, role, currentTheme);

