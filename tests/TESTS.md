# Test Specification

This document outlines the test specification for the Datastar module.

---

## Architecture Tests

### [Architecture](Architecture/ArchitectureTest.php)

_Tests the architecture of the plugin._

![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Source code does not contain any `Craft::dd` statements.  
![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Source code does not contain any `var_dump` or `die` statements.  

## Feature Tests

### [Sse](Feature/SseTest.php)

_Tests the SSE service._

![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Test remove elements tag.  
![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Test that calling an SSE method when another one is in process throws an exception.  

### [Variable](Feature/VariableTest.php)

_Tests the Datastar variable._

![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Test creating an action containing an array of primitive variables.  
![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Test that creating an action containing a reserved variable name throws an exception.  
![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Test that creating an action containing an object variable throws an exception.  
