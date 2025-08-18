# Test Specification

This document outlines the test specification for the Datastar module.

---

## Architecture Tests

### [Architecture](Architecture/ArchitectureTest.php)

_Tests the architecture of the plugin._

![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Source code does not contain any `Craft::dd` statements.  
![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Source code does not contain any `var_dump` or `die` statements.  

## Feature Tests

### [Action](Feature/ActionTest.php)

_Tests the Datastar action helper._

![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Test creating an action containing an array of primitive params.  
![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Test creating an action containing an array of options.  
![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Test creating an action containing an options string.  

### [AssetBundle](Feature/AssetBundleTest.php)

_Tests the Datastar asset bundle._

![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Test that the asset bundle uses the correct version.  

### [Config](Feature/ConfigTest.php)

_Tests the Datastar config model._

![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Test that creating a config model containing the signals variable name is invalid.  
![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Test that creating a config model containing an object param is invalid.  

### [Sse](Feature/SseTest.php)

_Tests the SSE service._

![Pass](https://raw.githubusercontent.com/putyourlightson/craft-generate-test-spec/main/icons/pass.svg) Test remove elements tag.  
