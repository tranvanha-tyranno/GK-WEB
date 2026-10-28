async (page) => {
 const videoPage=await page.context().newPage();
 try {
  await videoPage.goto('file:///C:/Users/tyranno/Documents/Project/GK-WEB/submission/reference-walkthrough.webm');
  await videoPage.locator('video').waitFor();
  await videoPage.waitForFunction(()=>document.querySelector('video').readyState>=1);
  const metadata=await videoPage.locator('video').evaluate(v=>({duration:v.duration,width:v.videoWidth,height:v.videoHeight}));
  await videoPage.locator('video').evaluate(async v=>{
    v.pause();v.currentTime=10;await new Promise(resolve=>v.addEventListener('seeked',resolve,{once:true}));
  });
  await videoPage.screenshot({path:'output/playwright/reference-video-frame.png'});
  return {status:'PASS',...metadata,sound:false,classification:'Supplementary silent UI reference. Does not satisfy final three-member spoken video requirement.'};
 } finally {await videoPage.close();}
}
