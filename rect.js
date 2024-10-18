let rectX, rectY, rectWidth, rectHeight, rectColorR, rectColorG, rectColorB;

function setup() {
  // createCanvas(400, 400);
  // background(200);
}

function draw() {
  // 毎フレームの描画を防ぐため、ここではdraw内に記述しません
}

function drawRectangle(x, y, w, h, cr, cg, cb) {
  const rate = 0.05;
  rectX = x;
  rectY = y*rate;
  rectWidth = w*rate;
  rectHeight = h;
  rectColorR = cr;
  rectColorG = cg;
  rectColorB = cb;
  fill(rectColorR, rectColorG, rectColorB);
  rect(rectX, rectY, rectWidth, rectHeight);
}
