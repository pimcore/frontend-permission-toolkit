import { test } from 'node:test'
import assert from 'node:assert/strict'
import { readFileSync, readdirSync, statSync } from 'node:fs'
import { join } from 'node:path'

const pkg = JSON.parse(readFileSync(new URL('../package.json', import.meta.url), 'utf8'))
const lock = JSON.parse(readFileSync(new URL('../package-lock.json', import.meta.url), 'utf8'))
const declared = { ...pkg.devDependencies, ...pkg.dependencies }
const min = (range) => range.replace(/^[\^~>=\s]+/, '').split('.').map((n) => parseInt(n, 10) || 0)
const atLeast = (range, wanted) => {
  const a = min(range); const b = wanted.split('.').map(Number)
  for (let i = 0; i < b.length; i++) { if ((a[i] ?? 0) !== b[i]) return (a[i] ?? 0) > b[i] }
  return true
}

test('AC1: manifest declares the LTS minimums', () => {
  const wanted = { antd: '5.29.3', '@rsbuild/core': '1.7.6', '@module-federation/rsbuild-plugin': '2.9.0' }
  for (const [name, v] of Object.entries(wanted)) {
    if (declared[name] !== undefined) assert.ok(atLeast(declared[name], v), `${name} ${declared[name]} < ${v}`)
  }
  assert.ok(declared.antd, 'antd declared')
  if (declared.jest !== undefined) assert.ok(atLeast(declared['@types/jest'] ?? '0', '30'), '@types/jest 30 next to jest')
})

test('AC2: directly imported packages are declared', () => {
  const found = new Set()
  const walk = (dir) => {
    for (const f of readdirSync(dir)) {
      const p = join(dir, f)
      if (statSync(p).isDirectory()) walk(p)
      else if (/\.(tsx?|jsx?)$/.test(f)) {
        for (const m of readFileSync(p, 'utf8').matchAll(/(?:from|import)\s+'(@[^/']+\/[^/']+|[^./@'][^/']*)/g)) found.add(m[1])
      }
    }
  }
  walk(new URL('../js', import.meta.url).pathname)
  for (const name of found) assert.ok(declared[name] !== undefined, `${name} imported but not declared`)
  assert.ok(declared.antd, 'antd declared')
})

test('AC3: lockfile resolves the LTS versions', () => {
  const v = (n) => lock.packages[`node_modules/${n}`]?.version
  assert.equal(v('antd'), '5.29.3')
  assert.equal(v('@rsbuild/core'), '1.7.6')
  assert.match(v('react'), /^18\.3\./)
  assert.equal(lock.packages[''].dependencies.antd, pkg.dependencies.antd, 'lock root matches manifest')
})
