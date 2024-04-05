<?php

namespace Timber\WpI18nTwig\Twig;

use Symfony\Bridge\Twig\TokenParser\DumpTokenParser;
use Symfony\Bridge\Twig\TokenParser\FormThemeTokenParser;
use Symfony\Bridge\Twig\TokenParser\StopwatchTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransDefaultDomainTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransTokenParser;
use Symfony\UX\TwigComponent\Twig\ComponentTokenParser as TwigComponentTokenParser;
use Symfony\UX\TwigComponent\Twig\PropsTokenParser;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Extra\Cache\TokenParser\CacheTokenParser;
use Twig\Loader\ArrayLoader;
use Twig\TokenParser\TokenParserInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * All credits goes to Vincent Langlet for this class.
 *
 * Provide stubs for all filters, functions, tests and tags that are not defined in twig's core.
 *
 * @see https://github.com/VincentLanglet/Twig-CS-Fixer/blob/35a824ec5c93189d983f5e95b85ab9b4f2ee59c8/src/Environment/StubbedEnvironment.php
 */
final class StubbedEnvironment extends Environment {

	/**
	 * @var array<string, TwigFilter|null>
	 */
	private $stub_filters = [];

	/**
	 * @var array<string, TwigFunction|null>
	 */
	private $stub_functions = [];

	/**
	 * @var array<string, TwigTest|null>
	 */
	private $stub_tests = [
		'divisible' => null, // Allow 'divisible by'
		'same'      => null, // Allow 'same as'
	];

	/**
	 * @param ExtensionInterface[]   $custom_twig_extensions
	 * @param TokenParserInterface[] $custom_token_parsers
	 */
	public function __construct(
		array $custom_twig_extensions = [],
		array $custom_token_parsers = []
	) {
		parent::__construct( new ArrayLoader() );

		$this->handleOptionalDependencies();

		foreach ( $custom_twig_extensions as $custom_twig_extension ) {
			$this->addExtension( $custom_twig_extension );
		}

		foreach ( $custom_token_parsers as $custom_token_parser ) {
			$this->addTokenParser( $custom_token_parser );
		}
	}

	/**
	 * @param string $name
	 */
	public function getFilter( $name ): ?TwigFilter {
		if ( ! \array_key_exists( $name, $this->stub_filters ) ) {
			$existing_filter             = parent::getFilter( $name );
			$this->stub_filters[ $name ] = $existing_filter instanceof TwigFilter
				? $existing_filter
				: new TwigFilter( $name );
		}

		return $this->stub_filters[ $name ];
	}

	/**
	 * @param string $name
	 */
	public function getFunction( $name ): ?TwigFunction {
		if ( ! \array_key_exists( $name, $this->stub_functions ) ) {
			$existing_function             = parent::getFunction( $name );
			$this->stub_functions[ $name ] = $existing_function instanceof TwigFunction
				? $existing_function
				: new TwigFunction( $name );
		}

		return $this->stub_functions[ $name ];
	}

	/**
	 * @param string $name
	 */
	public function getTest( $name ): ?TwigTest {
		if ( ! \array_key_exists( $name, $this->stub_tests ) ) {
			/** @psalm-suppress InternalMethod */
			$existing_test             = parent::getTest( $name );
			$this->stub_tests[ $name ] = $existing_test instanceof TwigTest
				? $existing_test
				: new TwigTest( $name );
		}

		return $this->stub_tests[ $name ];
	}

	private function handleOptionalDependencies(): void {
		if ( class_exists( DumpTokenParser::class ) ) {
			$this->addTokenParser( new DumpTokenParser() );
		}
		if ( class_exists( FormThemeTokenParser::class ) ) {
			$this->addTokenParser( new FormThemeTokenParser() );
		}
		if ( class_exists( StopwatchTokenParser::class ) ) {
			$this->addTokenParser( new StopwatchTokenParser( true ) );
		}
		if ( class_exists( TransDefaultDomainTokenParser::class ) ) {
			$this->addTokenParser( new TransDefaultDomainTokenParser() );
		}
		if ( class_exists( TransTokenParser::class ) ) {
			$this->addTokenParser( new TransTokenParser() );
		}
		if ( class_exists( CacheTokenParser::class ) ) {
			$this->addTokenParser( new CacheTokenParser() );
		}
		if ( class_exists( TwigComponentTokenParser::class ) ) {
			$this->addTokenParser( new ComponentTokenParser() );
		}
		if ( class_exists( PropsTokenParser::class ) ) {
			$this->addTokenParser( new PropsTokenParser() );
		}
	}
}
