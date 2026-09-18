class Pet {
  constructor(name, weight, ageStage, activityLevel, isNeutered) {
    this.name = name || 'สัตว์เลี้ยง';
    this.weight = Math.max(0.1, parseFloat(weight) || 5.0);
    this.ageStage = ageStage || 'adult';
    this.activityLevel = activityLevel || 'normal';
    this.isNeutered = Boolean(isNeutered);
  }
  getDERMultiplier() { return 1.6; }
  getSpeciesLabel() { return 'สัตว์เลี้ยง'; }
}

class Dog extends Pet {
  constructor(name, weight, ageStage, activityLevel, isNeutered, breedSize = 'medium') {
    super(name, weight, ageStage, activityLevel, isNeutered);
    this.breedSize = breedSize;
  }
  getSpeciesLabel() { return 'สุนัข'; }
  getDERMultiplier() {
    if (this.ageStage === 'puppy_kitten') return 2.5;
    let base = this.ageStage === 'senior' ? 1.4 : (this.isNeutered ? 1.6 : 1.8);
    if (this.activityLevel === 'low') base -= 0.2;
    if (this.activityLevel === 'high') base += 0.3;
    if (this.breedSize === 'small') base += 0.05;
    return Math.max(1.0, Math.round(base * 100) / 100);
  }
}

class Cat extends Pet {
  constructor(name, weight, ageStage, activityLevel, isNeutered, livingHabit = 'indoor') {
    super(name, weight, ageStage, activityLevel, isNeutered);
    this.livingHabit = livingHabit;
  }
  getSpeciesLabel() { return 'แมว'; }
  getDERMultiplier() {
    if (this.ageStage === 'puppy_kitten') return 2.5;
    let base = this.ageStage === 'senior' ? 1.1 : (this.isNeutered ? 1.2 : 1.4);
    if (this.livingHabit === 'indoor') base -= 0.1;
    if (this.livingHabit === 'outdoor') base += 0.2;
    if (this.activityLevel === 'low') base -= 0.1;
    if (this.activityLevel === 'high') base += 0.2;
    return Math.max(1.0, Math.round(base * 100) / 100);
  }
}

// ── 2. ก้อนอาหาร (Food) ──
class Food {
  constructor(name, foodType, caloriesPer100g) {
    this.name = name || 'อาหาร';
    this.foodType = foodType || 'dry';
    this.caloriesPer100g = Math.max(1, parseFloat(caloriesPer100g) || 380);
  }
  getCaloriesPerGram() {
    return this.caloriesPer100g / 100.0;
  }
}

// ── 3. ก้อนเครื่องคำนวณ (FeedingCalculator) ──
class FeedingCalculator {
  static calculateRER(weight) {
    return Math.round(70 * Math.pow(weight, 0.75) * 100) / 100;
  }
  static calculateDER(pet) {
    const rer = this.calculateRER(pet.weight);
    return Math.round(rer * pet.getDERMultiplier() * 100) / 100;
  }
  static calculateDailyPortion(pet, food, customTargetKcal = null) {
    const targetKcal = customTargetKcal !== null ? customTargetKcal : this.calculateDER(pet);
    return Math.round((targetKcal / food.getCaloriesPerGram()) * 10) / 10;
  }
}

// ── 4. ก้อนจัดตารางเวลา (FeedingSchedule) ──
class FeedingSchedule {
  constructor(mealsPerDay = 2, firstMealTime = '08:00') {
    this.mealsPerDay = Math.max(1, parseInt(mealsPerDay) || 2);
    this.firstMealTime = firstMealTime || '08:00';
  }
  calculatePortionPerMeal(dailyGrams) {
    return Math.round((dailyGrams / this.mealsPerDay) * 10) / 10;
  }
  generateSchedule() {
    const [h, m] = this.firstMealTime.split(':').map(Number);
    const startMins = (h || 8) * 60 + (m || 0);

    if (this.mealsPerDay === 1) {
      return [this.fmt(startMins)];
    }

    const spanMins = this.mealsPerDay >= 4 ? 720 : 600;
    const interval = Math.round(spanMins / (this.mealsPerDay - 1));

    const times = [];
    for (let i = 0; i < this.mealsPerDay; i++) {
      times.push(this.fmt(startMins + (i * interval)));
    }
    return times;
  }
  fmt(mins) {
    const norm = (mins % (24 * 60));
    const hh = String(Math.floor(norm / 60)).padStart(2, '0');
    const mm = String(norm % 60).padStart(2, '0');
    return `${hh}:${mm}`;
  }
  getMealSlots(dailyGrams) {
    const times = this.generateSchedule();
    const portion = this.calculatePortionPerMeal(dailyGrams);
    const names = {
      1: ['มื้อหลัก'],
      2: ['มื้อเช้า', 'มื้อเย็น'],
      3: ['มื้อเช้า', 'มื้อกลางวัน', 'มื้อเย็น'],
      4: ['มื้อเช้า', 'มื้อกลางวัน', 'มื้อบ่าย/เย็น', 'มื้อค่ำ']
    }[this.mealsPerDay] || times.map((_, i) => `มื้อที่ ${i + 1}`);

    return times.map((time, i) => ({
      mealName: names[i] || `มื้อที่ ${i + 1}`,
      time: time,
      portionGrams: portion
    }));
  }
}
