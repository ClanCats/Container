// PHPDoc method blocks for the VitePress build.
//
// Restores the legacy clancats.io behaviour: a `[~ PHPDoc](/src/File.php#method)`
// directive in the markdown is replaced at build time by a tabbed "functioninfo"
// block (Method / Arguments & Return) generated from the referenced PHP method's
// signature and docblock.
//
// The parser is pure JS (no PHP runtime — the docs CI is Node only). The docblock
// format used across `src/` is simple and regular, so a small set of regexes is
// enough.

import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

// docs/.vitepress/phpdoc.js -> repo root is two levels up.
const REPO_ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..')

// Only these repo-relative source links are treated as PHPDoc directives.
const HREF_RE = /^\/(src\/[A-Za-z0-9_/]+\.php)#([A-Za-z0-9_]+)$/

// Unique-per-build counter so each block's radio group / ids never collide.
let blockCounter = 0

function escapeHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
}

/**
 * Locate a method in a PHP source file and extract its signature + parsed docblock.
 * Returns null when the method (with a preceding docblock) cannot be found.
 */
function extractMethod(absFile, method) {
  let source
  try {
    source = fs.readFileSync(absFile, 'utf8')
  } catch {
    return null
  }

  // Immediately-preceding docblock + the signature up to the body `{` or `;`.
  // The docblock body is matched as "any char that doesn't start `*/`" so a
  // single self-contained block is captured — a plain `[\s\S]*?` would lazily
  // swallow every earlier docblock up to this method.
  const re = new RegExp(
    '/\\*\\*((?:[^*]|\\*(?!/))*)\\*/\\s*' +
      '((?:public|protected|private|static|final|abstract|\\s)*function\\s+' +
      method.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') +
      '\\s*\\([\\s\\S]*?)(?:\\{|;)',
  )
  const m = source.match(re)
  if (!m) return null

  const signature = m[2].replace(/\s+/g, ' ').trim()
  const doc = parseDocblock(m[1])
  return { signature, ...doc }
}

/** Parse the inner text of a `/** ... *​/` docblock. */
function parseDocblock(raw) {
  const lines = raw
    .split('\n')
    .map((l) => l.replace(/^\s*\*?\s?/, '').replace(/\s+$/, ''))

  const descriptionLines = []
  const params = []
  let ret = null

  for (const line of lines) {
    const param = line.match(/^@param\s+(\S+)\s+(\$\S+)?\s*(.*)$/)
    if (param) {
      params.push({ type: param[1], var: param[2] || '', comment: param[3].trim() })
      continue
    }
    const returns = line.match(/^@return\s+(\S+)\s*(.*)$/)
    if (returns) {
      ret = { type: returns[1], comment: returns[2].trim() }
      continue
    }
    if (line.startsWith('@')) continue // other tags (@var, @throws, ...) are ignored
    if (params.length === 0 && !ret) descriptionLines.push(line)
  }

  // Drop blank lines at the edges but keep internal indentation intact
  // (a plain String.trim() would eat a leading code example's indent).
  while (descriptionLines.length && descriptionLines[0].trim() === '') descriptionLines.shift()
  while (descriptionLines.length && descriptionLines[descriptionLines.length - 1].trim() === '')
    descriptionLines.pop()

  return {
    description: descriptionLines.join('\n'),
    params,
    return: ret,
  }
}

/**
 * Turn a docblock description into markdown. Prose stays as-is; runs of
 * 4-space / tab indented lines (the PHPDoc code-example convention) are dedented
 * and wrapped in ```php fences so they get syntax-highlighted like any other
 * code block. Blank lines only join a code run when more indented code follows.
 */
function descriptionToMarkdown(description) {
  const lines = description.split('\n')
  const out = []
  let i = 0
  while (i < lines.length) {
    if (/^( {4}|\t)/.test(lines[i])) {
      const code = []
      const pending = []
      while (i < lines.length) {
        if (/^( {4}|\t)/.test(lines[i])) {
          code.push(...pending.splice(0), lines[i].replace(/^( {4}|\t)/, ''))
          i++
        } else if (lines[i].trim() === '') {
          pending.push('')
          i++
        } else {
          break // non-indented prose ends the code run; trailing blanks dropped
        }
      }
      out.push('', '```php', ...code, '```', '')
    } else {
      out.push(lines[i])
      i++
    }
  }
  return out.join('\n')
}

/** Render the tabbed HTML block. `md` is the markdown-it instance (for highlighting). */
function renderBlock(info, md) {
  const id = `fi-${blockCounter++}`

  const sigHtml = md.render('```php\n' + info.signature + '\n```')

  const descHtml = info.description
    ? md.render(descriptionToMarkdown(info.description))
    : ''

  let argsHtml
  if (info.params.length) {
    const rows = info.params
      .map(
        (p) =>
          `<tr><td class="fi-type">${escapeHtml(p.type)}</td>` +
          `<td class="fi-var">${escapeHtml(p.var)}</td>` +
          `<td>${escapeHtml(p.comment)}</td></tr>`,
      )
      .join('')
    argsHtml =
      `<h4>Arguments</h4>` +
      `<table class="fi-table"><thead><tr>` +
      `<th>Data type</th><th>Variable name</th><th>Comment</th>` +
      `</tr></thead><tbody>${rows}</tbody></table>`
  } else {
    argsHtml = `<h4>Arguments</h4><p>This method takes no arguments.</p>`
  }

  const retHtml = info.return
    ? `<h4>Returns</h4><p><span class="fi-type">${escapeHtml(info.return.type)}</span>` +
      (info.return.comment ? ` ${escapeHtml(info.return.comment)}` : '') +
      `</p>`
    : ''

  return (
    `<div class="functioninfo">` +
    `<input type="radio" name="${id}" id="${id}-m" class="fi-radio fi-radio-m" checked>` +
    `<input type="radio" name="${id}" id="${id}-a" class="fi-radio fi-radio-a">` +
    `<div class="fi-tabs">` +
    `<label for="${id}-m" class="fi-tab fi-tab-m">Method</label>` +
    `<label for="${id}-a" class="fi-tab fi-tab-a">Arguments / Return</label>` +
    `</div>` +
    `<div class="fi-pane fi-pane-m">${descHtml}<h4>Method definition:</h4>${sigHtml}</div>` +
    `<div class="fi-pane fi-pane-a">${argsHtml}${retHtml}</div>` +
    `</div>`
  )
}

/** Fallback link kept when a method cannot be resolved, so the build never breaks. */
function fallbackLink(relPath, method) {
  const url = `https://github.com/ClanCats/Container/blob/master/${relPath}`
  return `<p><a href="${url}#${method}" target="_blank" rel="noreferrer">~ PHPDoc: ${escapeHtml(method)}</a></p>`
}

/** Find a `[~ PHPDoc](/src/...#method)` link inside an inline token's children. */
function findDirective(inlineToken) {
  const children = inlineToken.children || []
  for (const child of children) {
    if (child.type !== 'link_open') continue
    const href = child.attrGet('href') || ''
    const match = href.match(HREF_RE)
    if (match) return { relPath: match[1], method: match[2] }
  }
  return null
}

export default function phpDocPlugin(md) {
  md.core.ruler.push('phpdoc_blocks', (state) => {
    const tokens = state.tokens
    for (let i = 0; i < tokens.length; i++) {
      const token = tokens[i]
      if (token.type !== 'inline') continue

      const directive = findDirective(token)
      if (!directive) continue

      // The directive lives alone in a paragraph: paragraph_open / inline / paragraph_close.
      const openOk = tokens[i - 1] && tokens[i - 1].type === 'paragraph_open'
      const closeOk = tokens[i + 1] && tokens[i + 1].type === 'paragraph_close'
      if (!openOk || !closeOk) continue

      const absFile = path.join(REPO_ROOT, directive.relPath)
      const info = extractMethod(absFile, directive.method)

      const html = info
        ? renderBlock(info, md)
        : fallbackLink(directive.relPath, directive.method)

      const htmlToken = new state.Token('html_block', '', 0)
      htmlToken.content = html
      htmlToken.block = true

      tokens.splice(i - 1, 3, htmlToken)
      i -= 1 // step back onto the replacement
    }
  })
}
