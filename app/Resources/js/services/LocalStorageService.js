class LocalStorageService {

  constructor() {
    this.defaultCacheTime = 3600000;
  }

  isValid(key, cacheTime) {
    const cache = JSON.parse(localStorage.getItem(key));
    if (!cache) {
      return false;
    }
    const time = cacheTime || this.defaultCacheTime;
    return (new Date().getTime() - cache.timestamp) < time;
  }

  get(key) {
    const parsedJson = JSON.parse(localStorage.getItem(key));
    return parsedJson ? parsedJson.data : null;
  }

  set(key, data) {
    const cache = {
      data: data,
      timestamp: new Date().getTime(),
    };
    localStorage.setItem(key, JSON.stringify(cache));
  }

}

export default new LocalStorageService();
