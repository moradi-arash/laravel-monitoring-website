# Contributing to Laravel Website Monitoring Tool

Thank you for your interest in contributing to the Laravel Website Monitoring Tool! This document provides guidelines and information for contributors.

## 🤝 How to Contribute

### Reporting Bugs

If you find a bug, please create an issue with the following information:

1. **Bug Report Template**:
   - Clear and descriptive title
   - Steps to reproduce the bug
   - Expected behavior
   - Actual behavior
   - Screenshots (if applicable)
   - Environment details (PHP version, Laravel version, OS)
   - Error logs (if any)

2. **Before Reporting**:
   - Check if the issue already exists
   - Try the latest version
   - Search the documentation

### Suggesting Features

We welcome feature suggestions! Please:

1. **Feature Request Template**:
   - Clear and descriptive title
   - Detailed description of the feature
   - Use case and benefits
   - Implementation ideas (optional)
   - Screenshots or mockups (if applicable)

2. **Before Suggesting**:
   - Check if the feature already exists
   - Search existing issues and discussions
   - Consider if it fits the project's scope

## 🔧 Development Setup

### Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js and npm
- Git
- MySQL/SQLite

### Getting Started

1. **Fork the Repository**
   ```bash
   # Fork on GitHub, then clone your fork
   git clone https://github.com/your-username/laravel-monitoring-website.git
   cd laravel-monitoring-website
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Build Assets**
   ```bash
   npm run dev
   # or for production
   npm run build
   ```

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

## 📝 Code Style Guidelines

### PHP Code Style

We follow PSR-12 coding standards:

1. **Use Laravel Pint** (included):
   ```bash
   ./vendor/bin/pint
   ```

2. **Manual Guidelines**:
   - Use 4 spaces for indentation
   - Use camelCase for variables and methods
   - Use PascalCase for classes
   - Use snake_case for database columns
   - Add type hints where possible
   - Write meaningful variable and method names

### JavaScript Code Style

- Use 2 spaces for indentation
- Use single quotes for strings
- Use const/let instead of var
- Follow ESLint rules

### Blade Templates

- Use 4 spaces for indentation
- Keep templates clean and readable
- Use components when appropriate
- Add comments for complex logic

## 🧪 Testing Guidelines

### Writing Tests

1. **Test Structure**:
   - Write tests for new features
   - Test both success and failure scenarios
   - Use descriptive test names
   - Follow AAA pattern (Arrange, Act, Assert)

2. **Test Types**:
   - **Unit Tests**: Test individual methods and classes
   - **Feature Tests**: Test complete user workflows
   - **Integration Tests**: Test external service integrations

3. **Example Test**:
   ```php
   public function test_website_monitoring_creates_alert_on_failure()
   {
       // Arrange
       $website = Website::factory()->create();
       
       // Act
       $result = $this->artisan('website:check');
       
       // Assert
       $this->assertDatabaseHas('alerts', [
           'website_id' => $website->id,
           'status' => 'failed'
       ]);
   }
   ```

### Test Coverage

- Aim for at least 80% code coverage
- Focus on critical business logic
- Test error handling and edge cases

## 📋 Pull Request Process

### Before Submitting

1. **Checklist**:
   - [ ] Code follows style guidelines
   - [ ] Tests pass
   - [ ] New features have tests
   - [ ] Documentation is updated
   - [ ] No merge conflicts
   - [ ] Commit messages are clear

2. **Branch Naming**:
   - `feature/description` for new features
   - `bugfix/description` for bug fixes
   - `hotfix/description` for urgent fixes
   - `docs/description` for documentation

3. **Commit Messages**:
   ```
   type(scope): description
   
   Detailed description (optional)
   
   Closes #123
   ```
   
   Types: `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`

### Pull Request Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Tests pass
- [ ] New tests added
- [ ] Manual testing completed

## Checklist
- [ ] Code follows style guidelines
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] No breaking changes
```

## 🎯 Areas for Contribution

### High Priority

- **Performance Optimizations**: Database queries, caching, monitoring efficiency
- **New Alert Channels**: Email, Slack, Discord, webhooks
- **Enhanced Monitoring**: Response time tracking, SSL certificate monitoring
- **API Development**: RESTful API for external integrations
- **Docker Support**: Containerization and deployment

### Medium Priority

- **UI/UX Improvements**: Better dashboard, mobile responsiveness
- **Advanced Filtering**: Search, filtering, sorting in dashboard
- **Bulk Operations**: Mass website management
- **Reporting**: Analytics, uptime reports, trend analysis
- **Internationalization**: Multi-language support

### Low Priority

- **Documentation**: Code comments, API documentation
- **Testing**: Additional test coverage
- **Code Quality**: Refactoring, code organization
- **Security**: Security enhancements, vulnerability fixes

## 🐛 Bug Fixing

### Common Bug Categories

1. **Telegram Integration Issues**
   - Bot token validation
   - Chat ID resolution
   - Message formatting

2. **Monitoring Logic**
   - Website checking algorithms
   - Error detection accuracy
   - Timeout handling

3. **Database Issues**
   - Migration problems
   - Query optimization
   - Data integrity

4. **Frontend Issues**
   - JavaScript errors
   - CSS styling problems
   - Form validation

### Debugging Tips

1. **Enable Debug Mode**:
   ```env
   APP_DEBUG=true
   LOG_LEVEL=debug
   ```

2. **Check Logs**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Use Tinker**:
   ```bash
   php artisan tinker
   ```

## 📚 Documentation

### Code Documentation

- Add PHPDoc comments for classes and methods
- Document complex algorithms
- Include usage examples
- Explain business logic

### User Documentation

- Update README.md for new features
- Add configuration examples
- Include troubleshooting guides
- Create video tutorials (optional)

## 🚫 What Not to Contribute

- Code that doesn't follow style guidelines
- Features that don't align with project goals
- Breaking changes without discussion
- Code without tests
- Incomplete implementations

## 📞 Getting Help

### Communication Channels

- **GitHub Issues**: Bug reports and feature requests
- **GitHub Discussions**: General questions and ideas
- **Email**: [Your Email Address]

### Response Times

- Bug reports: 1-3 business days
- Feature requests: 1-2 weeks
- General questions: 2-5 business days

## 🏆 Recognition

Contributors will be recognized in:

- CONTRIBUTORS.md file
- Release notes
- Project documentation
- GitHub contributors list

## 📄 Code of Conduct

### Our Pledge

We are committed to providing a welcoming and inclusive environment for all contributors, regardless of:

- Age, body size, disability, ethnicity
- Gender identity and expression
- Level of experience, education
- Nationality, personal appearance
- Race, religion, sexual orientation

### Expected Behavior

- Use welcoming and inclusive language
- Be respectful of differing viewpoints
- Accept constructive criticism gracefully
- Focus on what's best for the community
- Show empathy towards other community members

### Unacceptable Behavior

- Harassment, trolling, or inflammatory comments
- Personal attacks or political discussions
- Public or private harassment
- Publishing private information without permission
- Unprofessional conduct

### Enforcement

Violations will be addressed through:

1. Warning
2. Temporary ban
3. Permanent ban

## 📝 License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

**Thank you for contributing to Laravel Website Monitoring Tool!** 🎉
