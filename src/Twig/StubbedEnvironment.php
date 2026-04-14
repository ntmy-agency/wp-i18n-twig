<?php

namespace Timber\WpI18nTwig\Twig;

use Symfony\Bridge\Twig\TokenParser\DumpTokenParser;
use Symfony\Bridge\Twig\TokenParser\FormThemeTokenParser;
use Symfony\Bridge\Twig\TokenParser\StopwatchTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransDefaultDomainTokenParser;
use Symfony\Bridge\Twig\TokenParser\TransTokenParser;
use Symfony\UX\TwigComponent\Twig\ComponentLexer;
use Symfony\UX\TwigComponent\Twig\ComponentTokenParser as TwigComponentTokenParser;
use Symfony\UX\TwigComponent\Twig\PropsTokenParser;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Extra\Cache\TokenParser\CacheTokenParser;
use Twig\Loader\ArrayLoader;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\TokenParser\TokenParserInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;
use TwigCsFixer\Environment\Parser\ComponentTokenParser;

/**
 * All credits goes to Vincent Langlet for this class.
 *
 * Provide stubs for all filters, functions, tests and tags that are not defined in twig's core.
 *
 * @see https://github.com/VincentLanglet/Twig-CS-Fixer/blob/6b236c344260d199c3f52fbc8fc9f4b1b4a19bb3/src/Environment/StubbedEnvironment.php
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
	 * @var array<int, TokenParserInterface|string>
	 */
	private static $optional_token_parsers = [];

	/**
	 * @var array<int, ExtensionInterface|string>
	 */
	private static $optional_extensions = [];

	/**
	 * @var array<int, NodeVisitorInterface|string>
	 */
	private static $optional_node_visitors = [];

	/**
	 * @var callable|null
	 *
	 * Signature:
	 * callable(StubbedEnvironment): Lexer
	 */
	private static $optional_lexer_factory = null;

	/**
	 * @param ExtensionInterface[]   $custom_twig_extensions
	 * @param TokenParserInterface[] $custom_token_parsers
	 * @param NodeVisitorInterface[] $custom_node_visitors
	 */
	public function __construct(
		array $custom_twig_extensions = [],
		array $custom_token_parsers = [],
		array $custom_node_visitors = []
	) {
		parent::__construct( new ArrayLoader() );

		$this->handleOptionalDependencies();

		foreach ( $custom_twig_extensions as $custom_twig_extension ) {
			$this->addExtension( $custom_twig_extension );
		}

		foreach ( $custom_token_parsers as $custom_token_parser ) {
			$this->addTokenParser( $custom_token_parser );
		}

		foreach ( $custom_node_visitors as $custom_node_visitor ) {
			$this->addNodeVisitor( $custom_node_visitor );
		}
	}

	/**
	 * Avoid dependency to composer/semver for twig version comparison.
	 */
	public static function satisfiesTwigVersion( int $major, int $minor = 0, int $patch = 0 ): bool {
		$version = explode( '.', self::VERSION );

		if ( $major < $version[0] ) {
			return true;
		}
		if ( $major > $version[0] ) {
			return false;
		}
		if ( $minor < $version[1] ) {
			return true;
		}
		if ( $minor > $version[1] ) {
			return false;
		}

		return $version[2] >= $patch;
	}

	/**
	 * @param TokenParserInterface|string $token_parser
	 */
	public static function registerOptionalTokenParser( $token_parser ): void {
		self::registerUnique( self::$optional_token_parsers, $token_parser );
	}

	/**
	 * @param array<int, TokenParserInterface|string> $token_parsers
	 */
	public static function registerOptionalTokenParsers( array $token_parsers ): void {
		foreach ( $token_parsers as $token_parser ) {
			self::registerOptionalTokenParser( $token_parser );
		}
	}

	/**
	 * @param ExtensionInterface|string $extension
	 */
	public static function registerOptionalExtension( $extension ): void {
		self::registerUnique( self::$optional_extensions, $extension );
	}

	/**
	 * @param array<int, ExtensionInterface|string> $extensions
	 */
	public static function registerOptionalExtensions( array $extensions ): void {
		foreach ( $extensions as $extension ) {
			self::registerOptionalExtension( $extension );
		}
	}

	/**
	 * @param NodeVisitorInterface|string $node_visitor
	 */
	public static function registerOptionalNodeVisitor( $node_visitor ): void {
		self::registerUnique( self::$optional_node_visitors, $node_visitor );
	}

	/**
	 * @param array<int, NodeVisitorInterface|string> $node_visitors
	 */
	public static function registerOptionalNodeVisitors( array $node_visitors ): void {
		foreach ( $node_visitors as $node_visitor ) {
			self::registerOptionalNodeVisitor( $node_visitor );
		}
	}

	/**
	 * @param callable $lexer_factory callable(StubbedEnvironment): Lexer
	 */
	public static function registerOptionalLexerFactory( callable $lexer_factory ): void {
		self::$optional_lexer_factory = $lexer_factory;
	}

	public static function resetOptionalDependencies(): void {
		self::$optional_token_parsers = [];
		self::$optional_extensions = [];
		self::$optional_node_visitors = [];
		self::$optional_lexer_factory = null;
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
			$existing_test             = parent::getTest( $name );
			$this->stub_tests[ $name ] = $existing_test instanceof TwigTest
				? $existing_test
				: new TwigTest( $name );
		}

		return $this->stub_tests[ $name ];
	}

	private function handleOptionalDependencies(): void {
		if ( class_exists( DumpTokenParser::class ) ) {
			// @phpstan-ignore argument.type
			$this->addTokenParser( new DumpTokenParser() );
		}
		if ( class_exists( FormThemeTokenParser::class ) ) {
			// @phpstan-ignore argument.type
			$this->addTokenParser( new FormThemeTokenParser() );
		}
		if ( class_exists( StopwatchTokenParser::class ) ) {
			// @phpstan-ignore argument.type
			$this->addTokenParser( new StopwatchTokenParser( true ) );
		}
		if ( class_exists( TransDefaultDomainTokenParser::class ) ) {
			// @phpstan-ignore argument.type
			$this->addTokenParser( new TransDefaultDomainTokenParser() );
		}
		if ( class_exists( TransTokenParser::class ) ) {
			// @phpstan-ignore argument.type
			$this->addTokenParser( new TransTokenParser() );
		}
		if ( class_exists( CacheTokenParser::class ) ) {
			// @phpstan-ignore argument.type
			$this->addTokenParser( new CacheTokenParser() );
		}
		if ( class_exists( TwigComponentTokenParser::class ) && class_exists( ComponentTokenParser::class ) ) {
			// @phpstan-ignore argument.type
			$this->addTokenParser( new ComponentTokenParser() );
		}
		if ( class_exists( PropsTokenParser::class ) ) {
			// @phpstan-ignore argument.type
			$this->addTokenParser( new PropsTokenParser() );
		}
		if ( class_exists( ComponentLexer::class ) ) {
			// @phpstan-ignore argument.type
			$this->setLexer( new ComponentLexer( $this ) );
		}

		foreach ( self::$optional_extensions as $extension ) {
			$extension = $this->resolveOptionalExtension( $extension );

			if ( $extension instanceof ExtensionInterface ) {
				$this->addExtension( $extension );
			}
		}

		foreach ( self::$optional_token_parsers as $token_parser ) {
			$token_parser = $this->resolveOptionalTokenParser( $token_parser );

			if ( $token_parser instanceof TokenParserInterface ) {
				$this->addTokenParser( $token_parser );
			}
		}

		foreach ( self::$optional_node_visitors as $node_visitor ) {
			$node_visitor = $this->resolveOptionalNodeVisitor( $node_visitor );

			if ( $node_visitor instanceof NodeVisitorInterface ) {
				$this->addNodeVisitor( $node_visitor );
			}
		}

		if ( \is_callable( self::$optional_lexer_factory ) ) {
			$lexer = \call_user_func( self::$optional_lexer_factory, $this );

			if ( $lexer instanceof Lexer ) {
				$this->setLexer( $lexer );
			}
		}
	}

	/**
	 * @param ExtensionInterface|string $extension
	 *
	 * @return ExtensionInterface|null
	 */
	private function resolveOptionalExtension( $extension ): ?ExtensionInterface {
		if ( $extension instanceof ExtensionInterface ) {
			return $extension;
		}

		if ( \is_string( $extension ) && class_exists( $extension ) ) {
			$extension = new $extension();

			if ( $extension instanceof ExtensionInterface ) {
				return $extension;
			}
		}

		return null;
	}

	/**
	 * @param TokenParserInterface|string $token_parser
	 *
	 * @return TokenParserInterface|null
	 */
	private function resolveOptionalTokenParser( $token_parser ): ?TokenParserInterface {
		if ( $token_parser instanceof TokenParserInterface ) {
			return $token_parser;
		}

		if ( \is_string( $token_parser ) && class_exists( $token_parser ) ) {
			$token_parser = new $token_parser();

			if ( $token_parser instanceof TokenParserInterface ) {
				return $token_parser;
			}
		}

		return null;
	}

	/**
	 * @param NodeVisitorInterface|string $node_visitor
	 *
	 * @return NodeVisitorInterface|null
	 */
	private function resolveOptionalNodeVisitor( $node_visitor ): ?NodeVisitorInterface {
		if ( $node_visitor instanceof NodeVisitorInterface ) {
			return $node_visitor;
		}

		if ( \is_string( $node_visitor ) && class_exists( $node_visitor ) ) {
			$node_visitor = new $node_visitor();

			if ( $node_visitor instanceof NodeVisitorInterface ) {
				return $node_visitor;
			}
		}

		return null;
	}

	/**
	 * @param array<int, mixed> $registry
	 * @param mixed             $value
	 */
	private static function registerUnique( array &$registry, $value ): void {
		foreach ( $registry as $registered ) {
			if ( $registered === $value ) {
				return;
			}

			if ( \is_string( $registered ) && \is_string( $value ) && $registered === $value ) {
				return;
			}

			if ( \is_object( $registered ) && \is_object( $value ) && \get_class( $registered ) === \get_class( $value ) ) {
				return;
			}
		}

		$registry[] = $value;
	}
}
