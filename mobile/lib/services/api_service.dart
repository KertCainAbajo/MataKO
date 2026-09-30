import 'dart:async';
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  // Browsers and desktop use localhost; Android emulators use the host alias 10.0.2.2.
  static String get baseUrl {
    final isDesktop = defaultTargetPlatform == TargetPlatform.windows ||
        defaultTargetPlatform == TargetPlatform.macOS ||
        defaultTargetPlatform == TargetPlatform.linux;
    return kIsWeb || isDesktop
        ? 'http://127.0.0.1:8000/api'
        : 'http://10.0.2.2:8000/api';
  }

  static const String tokenKey = 'matako_token';
  static const String _hasAccountKey = 'matako_has_account';
  static const String _preferredLanguageKey = 'matako_preferred_language';
  static const Duration _requestTimeout = Duration(seconds: 15);

  Future<bool> get hasRegisteredAccount async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getBool(_hasAccountKey) ?? false;
  }

  Future<String> get preferredLanguage async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_preferredLanguageKey) ?? 'English';
  }

  Future<void> savePreferredLanguage(String language) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_preferredLanguageKey, language);
  }

  Future<void> _markAccountExists() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_hasAccountKey, true);
  }

  Future<http.Response> _send(
    String method,
    Uri uri,
    Map<String, String> headers, {
    Map<String, dynamic>? body,
  }) async {
    try {
      final request = method == 'GET'
          ? http.get(uri, headers: headers)
          : http.post(uri, headers: headers, body: jsonEncode(body ?? {}));
      return await request.timeout(_requestTimeout);
    } on TimeoutException {
      throw Exception(
        'The MataKo server did not respond. Start the Laravel backend and try again.',
      );
    } on Exception catch (error) {
      final message = error.toString().toLowerCase();
      if (message.contains('socketexception') ||
          message.contains('clientexception') ||
          message.contains('failed host lookup')) {
        throw Exception(
          'Cannot connect to the MataKo server. Start the Laravel backend and try again.',
        );
      }
      rethrow;
    }
  }

  Future<String?> get token async =>
      (await SharedPreferences.getInstance()).getString(tokenKey);

  Future<void> saveToken(String value) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(tokenKey, value);
  }

  Future<void> clearToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(tokenKey);
  }

  Future<Map<String, dynamic>> _request(String method, String path,
      {Map<String, dynamic>? body, bool authenticated = false}) async {
    final headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    };
    if (authenticated) {
      final currentToken = await token;
      if (currentToken != null) {
        headers['Authorization'] = 'Bearer $currentToken';
      }
    }
    final uri = Uri.parse('$baseUrl$path');
    final response = await _send(method, uri, headers, body: body);

    final Map<String, dynamic> data = response.body.isEmpty
        ? <String, dynamic>{}
        : jsonDecode(response.body) as Map<String, dynamic>;
    if (response.statusCode < 200 || response.statusCode >= 300) {
      final errors = data['errors'] as Map<String, dynamic>?;
      final firstError =
          errors == null || errors.isEmpty ? null : errors.values.first;
      final message = firstError is List && firstError.isNotEmpty
          ? firstError.first.toString()
          : (data['message']?.toString() ??
              'Request failed. Please try again.');
      throw Exception(message);
    }
    return data;
  }

  Future<void> login(String email, String password) async {
    final data = await _request('POST', '/login',
        body: {'email': email, 'password': password});
    await saveToken(data['token'] as String);
    await _markAccountExists();
  }

  Future<void> register(
      {required String name,
      required String email,
      required String phone,
      required String password,
      required String passwordConfirmation,
      required int age,
      required String role}) async {
    final data = await _request('POST', '/register', body: {
      'name': name,
      'email': email,
      'phone': phone,
      'password': password,
      'password_confirmation': passwordConfirmation,
      'age': age,
      'role': role,
    });
    await saveToken(data['token'] as String);
    await _markAccountExists();
  }

  Future<Map<String, dynamic>> getDashboard() =>
      _request('GET', '/assessments', authenticated: true);

  Future<Map<String, dynamic>> getUser() =>
      _request('GET', '/user', authenticated: true);

  Future<Map<String, dynamic>> submitAssessment(Map<String, int> answers) =>
      _request('POST', '/assessment',
          body: {'answers': answers}, authenticated: true);
}
