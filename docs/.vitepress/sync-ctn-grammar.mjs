#!/usr/bin/env node
// Regenerate the docs `.ctn` Shiki grammar from the VSCode extension's TextMate
// grammar, so the two never drift.
//
//   node docs/.vitepress/sync-ctn-grammar.mjs [path-to.tmLanguage]
//   npm run docs:sync-grammar
//
// Source (default): ../../vsc-cccontainer/syntaxes/cccontainer.tmLanguage relative
// to the repo root, i.e. the sister extension repo. Override by passing a path as
// the first argument or via the CTN_GRAMMAR_SRC env var.
//
// Output: docs/.vitepress/ctn.tmLanguage.js (imported by config.js).
//
// The extension grammar is a plist/XML file; this parses the small subset of plist
// it uses (dict / array / key / string / integer / real / true / false) with no
// external dependencies, then emits a Shiki `LanguageRegistration` object.

import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const HERE = path.dirname(fileURLToPath(import.meta.url))
const DEFAULT_SRC = path.resolve(HERE, '../../../vsc-cccontainer/syntaxes/cccontainer.tmLanguage')
const OUT = path.resolve(HERE, 'ctn.tmLanguage.js')

const src = process.argv[2] || process.env.CTN_GRAMMAR_SRC || DEFAULT_SRC

function unescapeXml(t) {
  return t
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"')
    .replace(/&apos;/g, "'")
    .replace(/&amp;/g, '&')
}

function tokenize(xml) {
  const tokens = []
  const re = /<(\/?)([a-zA-Z]+)(\/?)>|([^<]+)/g
  let m
  while ((m = re.exec(xml))) {
    if (m[2]) tokens.push({ type: 'tag', close: m[1] === '/', name: m[2], selfClose: m[3] === '/' })
    else tokens.push({ type: 'text', value: m[4] })
  }
  return tokens
}

function parse(tokens) {
  let idx = 0
  const skipWs = () => {
    while (idx < tokens.length && tokens[idx].type === 'text' && tokens[idx].value.trim() === '') idx++
  }
  const readText = (name) => {
    let val = ''
    while (idx < tokens.length) {
      const t = tokens[idx]
      if (t.type === 'text') { val += t.value; idx++ }
      else if (t.type === 'tag' && t.close && t.name === name) { idx++; break }
      else throw new Error(`expected </${name}>`)
    }
    return unescapeXml(val)
  }
  function parseValue() {
    skipWs()
    const t = tokens[idx]
    if (!t || t.type !== 'tag') throw new Error('expected value element')
    if (t.selfClose) {
      idx++
      if (t.name === 'true') return true
      if (t.name === 'false') return false
      throw new Error(`unsupported self-closing <${t.name}/>`)
    }
    idx++
    switch (t.name) {
      case 'dict': return parseDict()
      case 'array': return parseArray()
      case 'string': return readText('string')
      case 'integer': return parseInt(readText('integer'), 10)
      case 'real': return parseFloat(readText('real'))
      case 'true': readText('true'); return true
      case 'false': readText('false'); return false
      default: throw new Error(`unsupported element <${t.name}>`)
    }
  }
  function parseDict() {
    const obj = {}
    while (idx < tokens.length) {
      skipWs()
      const t = tokens[idx]
      if (t.type === 'tag' && t.close && t.name === 'dict') { idx++; break }
      if (!(t.type === 'tag' && t.name === 'key')) throw new Error('expected <key> in dict')
      idx++
      const key = readText('key')
      obj[key] = parseValue()
    }
    return obj
  }
  function parseArray() {
    const arr = []
    while (idx < tokens.length) {
      skipWs()
      const t = tokens[idx]
      if (t.type === 'tag' && t.close && t.name === 'array') { idx++; break }
      arr.push(parseValue())
    }
    return arr
  }
  return parseValue()
}

let xml
try {
  xml = fs.readFileSync(src, 'utf8')
} catch {
  console.error(`Could not read grammar source:\n  ${src}\n`)
  console.error('Pass the path to cccontainer.tmLanguage as an argument or set CTN_GRAMMAR_SRC.')
  process.exit(1)
}

// Strip the bits our minimal parser does not model.
xml = xml
  .replace(/<\?xml[\s\S]*?\?>/g, '')
  .replace(/<!DOCTYPE[\s\S]*?>/g, '')
  .replace(/<!--[\s\S]*?-->/g, '')
  .replace(/<plist[^>]*>/g, '')
  .replace(/<\/plist>/g, '')

const root = parse(tokenize(xml))

if (!root.scopeName || !Array.isArray(root.patterns)) {
  console.error('Parsed grammar is missing scopeName / patterns — aborting.')
  process.exit(1)
}

// fileTypes -> Shiki aliases (drop the primary id and anything with a dot, which
// is not a valid language id).
const aliases = (root.fileTypes || []).filter((f) => f !== 'ctn' && !f.includes('.'))

const grammar = {
  name: 'ctn',
  scopeName: root.scopeName,
  aliases,
  patterns: root.patterns,
}

const relSrc = path.relative(path.resolve(HERE, '../..'), src)
const header =
  `// AUTO-GENERATED — do not edit by hand.\n` +
  `// Regenerate with:  npm run docs:sync-grammar\n` +
  `// Source: ${relSrc} (scope ${root.scopeName})\n` +
  `//\n` +
  `// This mirrors the grammar shipped by the VSCode extension so the docs and the\n` +
  `// editor highlight .ctn files identically. The docs build (CI / GitHub Pages)\n` +
  `// has no access to the extension repo, hence the committed copy.\n\n`

const body =
  `export const ctnGrammar = ${JSON.stringify(grammar, null, 2)}\n\n` +
  `export default ctnGrammar\n`

fs.writeFileSync(OUT, header + body)
console.log(`Wrote ${path.relative(process.cwd(), OUT)} (${grammar.patterns.length} top-level patterns) from ${relSrc}`)
