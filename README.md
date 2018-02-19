# Behat Speedtrap

[![Build Status](https://travis-ci.org/Brunty/behat-speedtrap.svg?branch=master)](https://travis-ci.org/Brunty/behat-speedtrap)

A smoke testing tool inspired by [symm/vape](https://github.com/symm/vape)

## Installation

Install via composer:

`composer require brunty/behat-speedtrap --dev`

## Configure

In your `behat.yml` file add the following extension configuration:

```yaml
default:
  extensions:
    Brunty\Behat\SpeedtrapExtension: ~
```

To configure the threshold for slow tests (default 2000ms) specify the configuration option:

```yaml
default:
  extensions:
    Brunty\Behat\SpeedtrapExtension:
      threshold: 500 # this is in ms
```

To configure the number of scenarios reported (default 10) specify the configuration option:

```yaml
default:
  extensions:
    Brunty\Behat\SpeedtrapExtension:
      report_length: 2 
```

## Contributing

This started as a small personal project.

Although this project is small, openness and inclusivity are taken seriously. To that end a code of conduct (listed in the contributing guide) has been adopted.

[Contributor Guide](CONTRIBUTING.md)
