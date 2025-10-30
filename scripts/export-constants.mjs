import fs from 'fs';
import path from 'path';

// ✅ 実体に合わせて assets/libs 配下を参照
import constants from '../src/assets/libs/Constants.js';

const outPath = path.resolve('./public/libs/constants.json');

// 念のため出力先ディレクトリを作成
fs.mkdirSync(path.dirname(outPath), { recursive: true });

// JSON 書き出し
fs.writeFileSync(outPath, JSON.stringify(constants, null, 2), 'utf-8');
console.log(`✅ Constants exported to ${outPath}`);