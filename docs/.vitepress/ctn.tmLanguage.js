// AUTO-GENERATED — do not edit by hand.
// Regenerate with:  npm run docs:sync-grammar
// Source: ../vsc-cccontainer/syntaxes/cccontainer.tmLanguage (scope source.cccontainer)
//
// This mirrors the grammar shipped by the VSCode extension so the docs and the
// editor highlight .ctn files identically. The docs build (CI / GitHub Pages)
// has no access to the extension repo, hence the committed copy.

export const ctnGrammar = {
  "name": "ctn",
  "scopeName": "source.cccontainer",
  "aliases": [
    "container"
  ],
  "patterns": [
    {
      "begin": "/\\*",
      "beginCaptures": {
        "0": {
          "name": "punctuation.definition.comment.begin.ctn"
        }
      },
      "end": "\\*/",
      "endCaptures": {
        "0": {
          "name": "punctuation.definition.comment.end.ctn"
        }
      },
      "name": "comment.block.ctn"
    },
    {
      "captures": {
        "1": {
          "name": "punctuation.definition.comment.ctn"
        }
      },
      "match": "(//).*$\\n?",
      "name": "comment.line.double-slash.ctn"
    },
    {
      "captures": {
        "1": {
          "name": "punctuation.definition.comment.ctn"
        }
      },
      "match": "^(#!).*$\\n?",
      "name": "comment.line.shebang.ctn"
    },
    {
      "captures": {
        "1": {
          "name": "punctuation.definition.comment.ctn"
        }
      },
      "match": "(#).*$\\n?",
      "name": "comment.line.number-sign.ctn"
    },
    {
      "match": "\\b(import|use|namespace|when|override)\\b",
      "name": "keyword.control.ctn"
    },
    {
      "begin": "([\"'])",
      "beginCaptures": {
        "0": {
          "name": "punctuation.definition.string.begin.ctn"
        }
      },
      "end": "([\"'])",
      "endCaptures": {
        "1": {
          "name": "punctuation.definition.string.end.ctn"
        }
      },
      "name": "string.quoted.ctn"
    },
    {
      "match": "(?xi)\n  (?:\\B[-+])?\n  (?:\n    (\n      \\B\\.[0-9]+|               # e.g. .999\n      \\b[0-9]+(\\.[0-9]*)?       # e.g. 999.999, 999. or 999\n    )(e[-+]?[0-9]+)?            # e.g. e+123, E-123\n  )",
      "name": "constant.numeric.ctn"
    },
    {
      "match": "\\:[a-zA-Z0-9_\\.]+",
      "name": "variable.parameter.ctn"
    },
    {
      "match": "\\@container+",
      "name": "support.variable.ctn"
    },
    {
      "match": "(\\@[a-zA-Z0-9_\\.]+)(<)([a-zA-Z0-9_,\\s]*?)(>)(\\s*)(:)(?:(\\s+)([A-Za-z_][A-Za-z0-9_\\\\]*))?",
      "captures": {
        "1": {
          "name": "variable.other.object.ctn"
        },
        "2": {
          "name": "punctuation.definition.generic.begin.ctn"
        },
        "3": {
          "name": "entity.name.type.ctn"
        },
        "4": {
          "name": "punctuation.definition.generic.end.ctn"
        },
        "6": {
          "name": "keyword.operator.assignment.ctn"
        },
        "8": {
          "name": "entity.name.class.ctn"
        }
      },
      "name": "meta.generic.definition.ctn"
    },
    {
      "begin": "(\\@[a-zA-Z0-9_\\.]+)(<)",
      "beginCaptures": {
        "1": {
          "name": "support.variable.function.ctn"
        },
        "2": {
          "name": "punctuation.definition.generic.begin.ctn"
        }
      },
      "end": "(>)",
      "endCaptures": {
        "0": {
          "name": "punctuation.definition.generic.end.ctn"
        }
      },
      "name": "meta.generic.materialization.ctn",
      "patterns": [
        {
          "begin": "([\"'])",
          "beginCaptures": {
            "0": {
              "name": "punctuation.definition.string.begin.ctn"
            }
          },
          "end": "([\"'])",
          "endCaptures": {
            "1": {
              "name": "punctuation.definition.string.end.ctn"
            }
          },
          "name": "string.quoted.ctn"
        },
        {
          "match": "(?xi)\n  (?:\\B[-+])?\n  (?:\n    (\n      \\B\\.[0-9]+|               # e.g. .999\n      \\b[0-9]+(\\.[0-9]*)?       # e.g. 999.999, 999. or 999\n    )(e[-+]?[0-9]+)?            # e.g. e+123, E-123\n  )",
          "name": "constant.numeric.ctn"
        },
        {
          "match": "\\@[a-zA-Z0-9_\\.]+",
          "name": "support.variable.function.ctn"
        },
        {
          "match": "\\:[a-zA-Z0-9_\\.]+",
          "name": "variable.parameter.ctn"
        },
        {
          "match": "[A-Za-z0-9_\\\\]+",
          "name": "entity.name.type.ctn"
        },
        {
          "match": ",",
          "name": "punctuation.separator.ctn"
        }
      ]
    },
    {
      "match": "(\\@[a-zA-Z0-9_\\.]+)(:)(\\s+)([A-Za-z_][A-Za-z0-9_\\\\]*)",
      "captures": {
        "1": {
          "name": "variable.other.object.ctn"
        },
        "2": {
          "name": "keyword.operator.assignment.ctn"
        },
        "4": {
          "name": "entity.name.class.ctn"
        }
      },
      "name": "meta.service.definition.ctn"
    },
    {
      "match": "\\@[a-zA-Z0-9_\\.]+:",
      "name": "variable.other.object.ctn"
    },
    {
      "match": "\\@[a-zA-Z0-9_\\.]+",
      "name": "support.variable.function.ctn"
    },
    {
      "match": "\\- ([a-zA-Z0-9_\\.]+)",
      "name": "entity.name.function.ctn"
    },
    {
      "match": "(?x)\n  :(?!:)",
      "name": "keyword.operator.assignment.ctn"
    }
  ]
}

export default ctnGrammar
