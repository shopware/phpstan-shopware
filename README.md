# PHPStan Rules for Shopware 6

[![Latest Version on Packagist](https://img.shields.io/packagist/v/shopwarelabs/phpstan-shopware.svg?style=flat-square)](https://packagist.org/packages/shopwarelabs/phpstan-shopware)
[![Total Downloads](https://img.shields.io/packagist/dt/shopwarelabs/phpstan-shopware.svg?style=flat-square)](https://packagist.org/packages/shopwarelabs/phpstan-shopware)
[![License](https://img.shields.io/github/license/shopwarelabs/phpstan-shopware.svg?style=flat-square)](https://github.com/shopwarelabs/phpstan-shopware/blob/main/LICENSE.md)

This package provides additional PHPStan rules for Shopware 6 projects. It helps developers catch common mistakes and enforce best practices specific to Shopware development.

## Installation

You can install the package via composer:

```bash
composer require --dev shopwarelabs/phpstan-shopware
```

## Usage

To use these rules, include the package's configuration file in your PHPStan configuration:

```neon
includes:
    - vendor/shopwarelabs/phpstan-shopware/rules.neon
```

or you use PHPStan Extension Installer

### Experimental future compatibility (opt-in)

This experimental ruleset validates an extension against announced Shopware BC changes as
well as the installed version. Its checks and configuration may change as we expand the
next-major compatibility analysis. Include it separately to opt in:

```neon
includes:
    - vendor/shopwarelabs/phpstan-shopware/future-compatibility.neon
```

It reports calls and extensions incompatible with next-major declarations announced by
Shopware's BC-change attributes. This includes references to old class names retained as aliases
after a class move. This ruleset is intentionally separate from `rules.neon`: adopting it means
choosing to prepare for the next major while remaining compatible with the current one.

PHPStan Extension Installer enables only `rules.neon`; it does not enable future compatibility automatically. Keep the explicit `future-compatibility.neon` include even when using the installer.

#### How announced changes are discovered

The future-compatibility rules use the BC-change attributes available in your currently installed Shopware version. Upgrading Shopware can therefore reveal additional future incompatibilities as new changes are announced. They do not check against a separately installed future release, activate runtime feature flags, or guarantee compatibility with changes that have not been announced in the installed source.

Discovery is usage-driven: PHPStan resolves a call or property access to its declaration, and the rules read that declaration's attributes through PHPStan's reflection API. Inheritance checks inspect parent declarations and the extension's overrides. Attribute names and arguments are inspected without instantiating the attributes; there is no generated catalogue or scan of every Shopware class.

Each rule explicitly supports particular attributes from `Shopware\Core\Framework\Deprecation\BCChange`. A new attribute does not automatically become a compatibility check. Class moves are a special case: the rules use the installed Core's `ClassAliasRegistry::ALIASES`, rather than scanning `#[ClassMoved]` attributes.

## Features

- Custom rules for Shopware 6.5 specific patterns
- Improved type inference for Shopware core classes
- Additional checks for common Shopware development pitfalls

### Available Rules

Here's a comprehensive list of all available rules:

1. **NoSuperglobalsRule**: Prevents usage of superglobals (`$_GET`, `$_POST`, `$_FILES`, `$_REQUEST`). Use proper request objects instead.

2. **DisallowFunctionsRule**: Prevents usage of certain disallowed functions in the codebase.

3. **NoEntityRepositoryInLoopRule**: Prevents EntityRepository method calls within loops to avoid N+1 query problems.

4. **NoSessionInPaymentHandlerAndStoreApiRule**: Prevents usage of session in payment handlers and Store API contexts.

5. **NoSymfonySessionInConstructorRule**: Prevents injection of Symfony Session in constructor to avoid early session starts.

6. **ForbidGlobBraceRule**: Prevents usage of glob brace expansion for better cross-platform compatibility.

7. **InternalClassExtendsRule**: Ensures proper extension of internal classes.

8. **NoUserEntityGetStoreTokenRule**: Prevents direct access to store tokens from User entities.

9. **MethodBecomesAbstractRule**: Checks for methods that should be abstract.

10. **ClassExtendUsesAbstractClassWhenExisting**: Enforces the use of abstract classes when they exist.

11. **NoDALFilterByID**: Prevents direct ID filtering in DAL queries.

12. **ScheduledTaskTooLowIntervalRule**: Ensures scheduled tasks don't have too low intervals.

13. **DisallowDefaultContextCreation**: Prevents creation of default contexts in inappropriate places.

14. **SetForeignKeyRule**: Enforces proper foreign key handling.

15. **InternalFunctionCallRule**: Controls usage of internal functions.

16. **InternalMethodCallRule**: Controls usage of internal methods.

17. **DisallowSessionFunctionsRule**: Prevents usage of session functions (`session_write_close`, `session_start`, `session_destroy`). Use the Symfony Session component instead.

18. **ForbidLocalDiskWriteRule**: Prevents local disk write operations (`file_put_contents`, `fopen` with write mode, `mkdir`, `unlink`, etc.). Use the temporary directory or Flysystem instead.

19. **ForwardSalesChannelContextToSystemConfigServiceRule**: Ensures that when a method has a SalesChannelContext parameter, it is forwarded to SystemConfigService methods as the salesChannelId argument.

20. **ForbidPredictableSaltRule**: Prevents hardcoded salts in `crypt()` and `password_hash()` calls, which are predictable and weaken security.

21. **ForbidWeakCryptoKeyRule**: Prevents weak cryptographic key sizes in `openssl_pkey_new()` calls. RSA keys must be at least 2048 bits.

22. **ForbidInsecureCookieRule**: Prevents setting cookies without the secure flag in `setcookie()` and `setrawcookie()` calls. Cookies should use `secure=true` for HTTPS-only transmission.

23. **ForbidInsecureSymfonyCookieRule**: Prevents creating Symfony `Cookie` objects without explicit `secure=true`. Detects `new Cookie(...)`, `Cookie::create(...)`, and `->withSecure(false)` calls. The `$secure` parameter must be explicitly set to `true` for HTTPS-only transmission.

24. **ForbidDisabledSslVerificationRule**: Prevents disabling SSL/TLS certificate verification in `curl_setopt()` and `stream_context_create()` calls, which allows man-in-the-middle attacks.

25. **NoEmptyResponseRule**: Detects Response class/subclass instantiations with empty or missing body content. Checks classes where the first constructor parameter represents the response body (e.g. `Response`, `JsonResponse`, `JsonApiResponse`) and allows empty bodies for status codes like 204, 301, 302, 304, 307, and 308.

### Future Compatibility Rules (opt-in)

The following checks and type extensions are registered only by `future-compatibility.neon`. They complement the ordinary checks for the currently installed API, rather than replacing them. Custom diagnostics use the `shopware.futureIncompatibility.` prefix; the identifiers below list its suffixes.

#### FutureCallSiteRule

Checks method/static calls, construction, and property visibility against announced changes:

| Attribute | Check | Diagnostic suffix |
| --- | --- | --- |
| `BecomesInternal` | Calling methods or constructing/calling classes that will become internal | `methodBecomesInternal`, `classBecomesInternal` |
| `VisibilityChange` | Method/property access from a scope that will no longer be allowed | `methodVisibilityChange`, `propertyVisibilityChange` |
| `ParameterRemoval` | Passing a parameter scheduled for removal | `parameterRemoval` |
| `ParameterNameChange` | Using the old name in a named argument | `parameterNameChange` |
| `NewRequiredParameter` | Omitting a future required parameter or supplying an incompatible value | `newRequiredParameter`, `newParameterType` |
| `NewOptionalParameter` | Supplying an incompatible value for an announced optional parameter | `newParameterType` |
| `ParameterDefaultValueChange` | Omitting an argument whose default will change | `parameterDefaultValueChange` |
| `ParameterTypeNarrowing` | Passing a value incompatible with the announced narrower type | `parameterTypeNarrowing` |

For example, when Core announces a required string `$locale` after the current `$id` parameter:

```php
$service->load($id); // Reports shopware.futureIncompatibility.newRequiredParameter.
$service->load($id, 'en_GB'); // Compatible positional call when Core provides a legacy shim.
$service->load($id, 123); // Reports shopware.futureIncompatibility.newParameterType.
```

`AnnouncedParameterCountIgnoreExtension` allows positional extra arguments covered by `NewRequiredParameter` or `NewOptionalParameter` without broadly suppressing `arguments.count`. `FutureCallSiteRule` still checks their types and future requiredness. This does not make the native signature variadic or add support for named arguments to a positional shim. The count-error exception does not apply to calls with named arguments or unpacking; announced-parameter checks also skip unpacked calls because the argument count is unknown. Multiple new parameters follow attribute declaration order after the existing parameters. Types that cannot be resolved are not checked.

#### FutureExtensionRule

Checks subclasses and overrides against changes announced on parent declarations:

| Attribute | Check | Diagnostic suffix |
| --- | --- | --- |
| `BecomesFinal` | Extending a class that will become final | `extendsClassBecomingFinal` |
| `BecomesInternal` | Extending a class that will become internal | `extendsClassBecomingInternal` |
| `BecomesAbstract` | Missing a concrete implementation of a method that will become abstract | `missingBecomesAbstractMethodImplementation` |
| `NewOptionalParameter` | An override missing the announced optional parameter | `missingNewOptionalParameterInOverride` |
| `ParameterTypeWidening` | An override accepting less than the announced parameter type | `parameterTypeWideningInOverride` |
| `ReturnTypeNarrowing` | An override returning more than the announced return type allows | `returnTypeNarrowingInOverride` |
| `BecomesReadonly`, `PropertyTypeNarrowing`, `PropertyTypeWidening`, private `VisibilityChange` | Redeclaring a parent property with an incompatible future contract | `propertyRedeclaration` |

For example, if a parent announces that `$id` will widen from `string` to `?string`, update the override now; accepting the wider type is compatible with the current parent too:

```diff
-public function load(string $id): void
+public function load(?string $id): void
```

Some changes, such as a parent becoming final, cannot be solved by adjusting an override. Stop extending that class instead.

#### FuturePropertyCompatibilityRule

Checks assignments to properties declared outside the current class. `PropertyTypeNarrowing` reports `propertyTypeNarrowing` when an assigned value is incompatible with the future type. `BecomesReadonly` reports `propertyBecomesReadonly` when assigning outside the declaring class. Static property assignments are checked too.

For example, if a property's announced type narrows from `int|string` to `int`:

```php
$entity->position = '1'; // Reports shopware.futureIncompatibility.propertyTypeNarrowing.
$entity->position = 1; // Compatible with both types.
```

#### FutureClassMovedUsageRule and FutureClassMovedExpressionUsageRule

Report `shopware.futureIncompatibility.classMoved` when source references an old class name registered in Core's `ClassAliasRegistry::ALIASES`. The two rules cover class-name nodes and expressions such as construction, static calls/property access, and `instanceof`. The old alias may still work at runtime; replace the reference with the canonical class name now.

```diff
-use Shopware\Core\Checkout\Document\DocumentDefinition;
+use Shopware\Core\Checkout\DocumentV2\DocumentDefinition;
```

#### FutureReturnTypeExtension and FuturePropertyTypeExtension

These support ordinary PHPStan analysis rather than emitting their own diagnostics. `ReturnTypeWidening` supplies the announced type for method-call expressions; `PropertyTypeWidening` does the same for property reads. Consequently, downstream operations can produce ordinary PHPStan errors even though the installed declaration still has the narrower type.

For example, if a method currently returns `string` but announces `?string`, account for `null` before passing the result to code requiring `string`:

```php
$label = $service->getLabel();
strlen($label); // PHPStan reports that the announced value can be null.
if ($label !== null) {
    strlen($label); // Accepts both the current and announced return type.
}
```

## Configuration

You can customize the behavior of these rules by adding configuration to your `phpstan.neon` file. See the [configuration section](#configuration) for more details.

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
