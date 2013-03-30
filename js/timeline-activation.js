if(timelines){
  for(i = 0, arrayLength = timelines.length; i < arrayLength; i++){
    createStoryJS({
      type: 'timeline',
      width: timelines[i].width,
      height: timelines[i].height,
      source: timelines[i].src,
      embed_id: timelines[i].timeline
    });
  }
}
