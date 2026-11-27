<?php
declare(strict_types=1);

/**
 * Verifies that there is no whitespace between the "function" keyword of a
 * closure and its opening parenthesis, e.g. static function( $x ) {}.
 */

namespace EpiphytCodingStandard\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class ClosureKeywordSpacingSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array<int|string>
     */
    public function register(): array {
        return [ \T_CLOSURE ];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param   \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param   int                         $stackPtr The position of the current token in the
     *                                               stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr): void {
        $tokens = $phpcsFile->getTokens();

        if (! isset($tokens[$stackPtr]['parenthesis_opener'])) {
            return;
        }

        // Whitespace directly before the opening parenthesis, which is after
        // the keyword or after the reference operator of function &( $x ).
        $opener = $tokens[$stackPtr]['parenthesis_opener'];
        $first = $opener;

        while ($tokens[($first - 1)]['code'] === \T_WHITESPACE) {
            $first--;
        }

        if ($first === $opener) {
            return;
        }

        $whitespace = $phpcsFile->getTokensAsString($first, ($opener - $first));
        $found = (string) \strlen($whitespace);

        if (\strpos($whitespace, $phpcsFile->eolChar) !== false) {
            $found = 'newline';
        }

        $error = 'Expected 0 spaces before the opening parenthesis of a closure; %s found';
        $fix = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceBeforeParenthesis', [ $found ]);

        if (! $fix) {
            return;
        }

        $phpcsFile->fixer->beginChangeset();

        for ($i = $first; $i < $opener; $i++) {
            $phpcsFile->fixer->replaceToken($i, '');
        }

        $phpcsFile->fixer->endChangeset();
    }
}
