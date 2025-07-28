<template>
  <Head title="Absensi QR Code | 25-ji, Nightcord de." />
  <div class="attendance-container">
    <!-- Animated particles background -->
    <div class="particles">
      <div v-for="particle in particles" :key="particle.id" class="particle" :style="{
        width: particle.size + 'px',
        height: particle.size + 'px',
        left: particle.left + '%',
        top: particle.top + '%',
        animationDelay: particle.delay + 's',
        animationDuration: particle.duration + 's'
      }"></div>
    </div>

    <!-- Navigation -->
    <!-- <NavBar /> -->

    <!-- Main content -->
    <main class="main-content">
      <!-- Header Section -->
      <section class="header-section fade-in">
        <div class="header-content">
          <div class="icon-container">
            <QrCode class="header-icon" />
          </div>
          <h1>Absensi Digital N25</h1>
          <p class="subtitle">Scan QR Code untuk melakukan absensi kehadiran</p>
        </div>
      </section>

      <!-- QR Code Section -->
      <section class="qr-section fade-in">
        <div class="qr-container">
          <!-- QR Code Display -->
          <div class="qr-wrapper">
            <div class="qr-frame">
              <div class="qr-corners">
                <div class="corner top-left"></div>
                <div class="corner top-right"></div>
                <div class="corner bottom-left"></div>
                <div class="corner bottom-right"></div>
              </div>
              
              <!-- QR Code Canvas -->
              <!-- <canvas 
                ref="qrCanvas" 
                class="qr-canvas"
                :width="qrSize" 
                :height="qrSize"
              ></canvas> -->

              <div v-html="qrCode">
              </div>
                
              
              
              <!-- Scanning animation overlay -->
              <div class="scan-line" :class="{ 'scanning': isScanning }"></div>
            </div>
            
            <!-- QR Code Info -->
            <div class="qr-info">
              <h3>{{ attendanceData.sessionName }}</h3>
              <p class="session-time">{{ formatTime(attendanceData.timestamp) }}</p>
              <p class="session-id">ID Sesi: {{ attendanceData.sessionId }}</p>
            </div>
          </div>

          <!-- Instructions -->
          <form @submit.prevent="submit" class="instructions">
            <div class="instruction-item">
              <div class="step-number">1</div>
              <div class="step-content">
                <h4>Pilih Shift</h4>
                <select v-model="form.shift" required class="select-option" >
                  <option v-for="shift in shifts" :key="shift.id" :value="shift.name">{{ shift.name }}</option>
                </select>
              </div>
            </div>
            <div class="instruction-item">
              <div class="step-number">2</div>
              <div class="step-content">
                <h4>Pilih Divisi</h4>
                <select v-model="form.division" required class="select-option">
                  <option v-for="division in divisions" :key="division.id" :value="division.name">{{ division.name }}</option>
                </select>
              </div>
            </div>
            
            <!-- <div class="instruction-item">
              <div class="step-number">2</div>
              <div class="step-content">
                <h4>Arahkan ke QR Code</h4>
                <p>Arahkan kamera ke QR Code di atas</p>
              </div>
            </div>
            
            <div class="instruction-item">
              <div class="step-number">3</div>
              <div class="step-content">
                <h4>Tap Notifikasi</h4>
                <p>Ketuk notifikasi yang muncul untuk melakukan absensi</p>
              </div>
            </div> -->

            <button type="submit" class="submit" :disabled="form.processing">Buat QrCode</button>
          </form>
        </div>
      </section>

      <!-- <section class="status-section fade-in">
        <div class="status-grid">
          <div class="status-card">
            <div class="status-icon">
              <Users class="icon" />
            </div>
            <div class="status-content">
              <h3>{{ attendanceStats.present }}</h3>
              <p>Hadir Hari Ini</p>
            </div>
          </div>
          
          <div class="status-card">
            <div class="status-icon">
              <Clock class="icon" />
            </div>
            <div class="status-content">
              <h3>{{ attendanceStats.onTime }}</h3>
              <p>Tepat Waktu</p>
            </div>
          </div>
          
          <div class="status-card">
            <div class="status-icon">
              <Calendar class="icon" />
            </div>
            <div class="status-content">
              <h3>{{ attendanceStats.totalSessions }}</h3>
              <p>Total Sesi</p>
            </div>
          </div>
        </div>
      </section>

      <section class="activity-section fade-in">
        <h2>Aktivitas Terbaru</h2>
        <div class="activity-list">
          <div 
            v-for="activity in recentActivities" 
            :key="activity.id" 
            class="activity-item"
          >
            <div class="activity-avatar">
              {{ getInitials(activity.name) }}
            </div>
            <div class="activity-content">
              <h4>{{ activity.name }}</h4>
              <p>{{ activity.action }}</p>
              <span class="activity-time">{{ formatRelativeTime(activity.timestamp) }}</span>
            </div>
            <div class="activity-status" :class="activity.status">
              <div class="status-dot"></div>
              <span>{{ activity.statusText }}</span>
            </div>
          </div>
        </div>
      </section> -->
    </main>

    <!-- Footer -->
    <!-- <footer class="footer">
      <p>&copy; 2025 25-ji, Nightcord de. | Sistem Absensi Digital</p>
    </footer> -->
  </div>
</template>

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { QrCode, Users, Clock, Calendar } from 'lucide-vue-next';
import { Division, Shift } from '@/types';
// import Qrcodevue from 'qrcode.vue';
// import NavBar from '@/components/NavAccount.vue';

interface Particle {
  id: number;
  size: number;
  left: number;
  top: number;
  delay: number;
  duration: number;
}

interface AttendanceData {
  sessionId: string;
  sessionName: string;
  timestamp: Date;
  qrData: string;
}

interface AttendanceStats {
  present: number;
  onTime: number;
  totalSessions: number;
}

interface Activity {
  id: number;
  name: string;
  action: string;
  timestamp: Date;
  status: 'present' | 'late' | 'absent';
  statusText: string;
}
interface Props {
  shifts: Shift[],
  divisions: Division[],
  qrCode: String,
}

// Reactive state

const form = useForm({
  shift: '',
  division: '',
  expires_in_minutes: 5,
});

const props = defineProps<Props>();
const particles = ref<Particle[]>([]);
const qrCanvas = ref<HTMLCanvasElement | null>(null);
const isScanning = ref(false);
const qrSize = ref(280);
console.log(props.qrCode);

// Attendance data
const attendanceData = ref<AttendanceData>({
  sessionId: 'N25-' + Date.now().toString(36),
  sessionName: 'Latihan Malam N25',
  timestamp: new Date(),
  qrData: ''
});

const attendanceStats = ref<AttendanceStats>({
  present: 12,
  onTime: 10,
  totalSessions: 45
});

const recentActivities = ref<Activity[]>([
  {
    id: 1,
    name: 'Kanade Yoisaki',
    action: 'Melakukan absensi kehadiran',
    timestamp: new Date(Date.now() - 2 * 60 * 1000),
    status: 'present',
    statusText: 'Hadir'
  },
  {
    id: 2,
    name: 'Mafuyu Asahina',
    action: 'Melakukan absensi kehadiran',
    timestamp: new Date(Date.now() - 5 * 60 * 1000),
    status: 'present',
    statusText: 'Hadir'
  },
  {
    id: 3,
    name: 'Ena Shinonome',
    action: 'Melakukan absensi kehadiran',
    timestamp: new Date(Date.now() - 15 * 60 * 1000),
    status: 'late',
    statusText: 'Terlambat'
  },
  {
    id: 4,
    name: 'Mizuki Akiyama',
    action: 'Melakukan absensi kehadiran',
    timestamp: new Date(Date.now() - 30 * 60 * 1000),
    status: 'present',
    statusText: 'Hadir'
  }
]);

// Methods
const createParticles = () => {
  const particleCount = 25;
  const newParticles = [];

  for (let i = 0; i < particleCount; i++) {
    const particle: Particle = {
      id: i,
      size: Math.random() * 4 + 2,
      left: Math.random() * 100,
      top: Math.random() * 100,
      delay: Math.random() * 6,
      duration: Math.random() * 3 + 4
    };
    newParticles.push(particle);
  }
  particles.value = newParticles;
};

const generateQRCode = () => {
  if (!qrCanvas.value) return;

  const canvas = qrCanvas.value;
  const ctx = canvas.getContext('2d');
  if (!ctx) return;

  // Generate QR data URL with attendance information
  const qrData = JSON.stringify({
    sessionId: attendanceData.value.sessionId,
    sessionName: attendanceData.value.sessionName,
    timestamp: attendanceData.value.timestamp.toISOString(),
    url: `${window.location.origin}/attendance/scan/${attendanceData.value.sessionId}`
  });

  attendanceData.value.qrData = qrData;

  // Simple QR code pattern simulation (in real app, use QR library like qrcode.js)
  const size = qrSize.value;
  const moduleSize = Math.floor(size / 25);
  
  // Clear canvas
  ctx.fillStyle = '#ffffff';
  ctx.fillRect(0, 0, size, size);
  
  // Create QR pattern
  ctx.fillStyle = '#000000';
  
  // Generate a simple pattern that looks like QR code
  for (let i = 0; i < 25; i++) {
    for (let j = 0; j < 25; j++) {
      // Create a pseudo-random pattern based on session data
      const shouldFill = (i + j + attendanceData.value.sessionId.charCodeAt(0)) % 3 === 0 ||
                        (i === 0 || i === 24 || j === 0 || j === 24) ||
                        (i < 8 && j < 8) || (i < 8 && j > 16) || (i > 16 && j < 8);
      
      if (shouldFill) {
        ctx.fillRect(i * moduleSize, j * moduleSize, moduleSize, moduleSize);
      }
    }
  }

  // Add N25 logo in center
  ctx.fillStyle = '#9d4edd';
  ctx.fillRect(10 * moduleSize, 10 * moduleSize, 5 * moduleSize, 5 * moduleSize);
  
  ctx.fillStyle = '#ffffff';
  ctx.font = `${moduleSize}px Arial`;
  ctx.textAlign = 'center';
  ctx.fillText('N25', 12.5 * moduleSize, 13 * moduleSize);
};

const startScanning = () => {
  isScanning.value = true;
  setTimeout(() => {
    isScanning.value = false;
  }, 3000);
};

const formatTime = (date: Date): string => {
  return date.toLocaleString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const formatRelativeTime = (date: Date): string => {
  const now = new Date();
  const diff = now.getTime() - date.getTime();
  const minutes = Math.floor(diff / 60000);
  
  if (minutes < 1) return 'Baru saja';
  if (minutes < 60) return `${minutes} menit yang lalu`;
  
  const hours = Math.floor(minutes / 60);
  if (hours < 24) return `${hours} jam yang lalu`;
  
  const days = Math.floor(hours / 24);
  return `${days} hari yang lalu`;
};

const getInitials = (name: string): string => {
  return name
    .split(' ')
    .map(word => word[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
};

const submit = () => {
  form.post(route('qrcode.create'), {
    onSuccess: () => {
      // Reset form after successful submission
      form.reset();
      // Optionally, you can show a success message or redirect
      console.log('QR Code created successfully');
    },
    onError: (errors) => {
      console.error('Error creating QR Code:', errors);
    }
  });
}

// Lifecycle hooks
onMounted(() => {
  createParticles();
  generateQRCode();
  
  // Auto refresh QR code every 5 minutes
  const refreshInterval = setInterval(() => {
    attendanceData.value.timestamp = new Date();
    attendanceData.value.sessionId = 'N25-' + Date.now().toString(36);
    generateQRCode();
  }, 5 * 60 * 1000);

  // Start scanning animation every 10 seconds
  const scanInterval = setInterval(startScanning, 10000);

  onUnmounted(() => {
    clearInterval(refreshInterval);
    clearInterval(scanInterval);
  });
});
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.submit {
  background: linear-gradient(45deg, #9d4edd, #c77dff);
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 500;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

.submit:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(157, 78, 221, 0.4);
}

.attendance-container {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  background: linear-gradient(135deg, #0a0a0a 0%, #1a0825 50%, #2d1b3d 100%);
  color: #ffffff;
  min-height: 100vh;
  position: relative;
  overflow-x: hidden;
}

/* Animated background particles */
.particles {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 1;
}

.particle {
  position: absolute;
  background: #9d4edd;
  border-radius: 50%;
  animation: float 6s ease-in-out infinite;
  opacity: 0.3;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
    opacity: 0.3;
  }
  50% {
    transform: translateY(-20px) rotate(180deg);
    opacity: 0.6;
  }
}

/* Main content */
.main-content {
  position: relative;
  z-index: 10;
  padding-top: 40px;
  padding-bottom: 2rem;
}

/* Header section */
.header-section {
  text-align: center;
  padding: 3rem rem;
  margin-bottom: 2rem;
}

.header-content {
  max-width: 600px;
  margin: 0 auto;
}

.icon-container {
  display: inline-flex;
  padding: 1.5rem;
  background: linear-gradient(45deg, #9d4edd, #c77dff);
  border-radius: 50%;
  margin-bottom: 2rem;
  box-shadow: 0 10px 30px rgba(157, 78, 221, 0.4);
}

.header-icon {
  width: 48px;
  height: 48px;
  color: white;
}

.header-section h1 {
  font-size: 3.5rem;
  font-weight: bold;
  margin-bottom: 1rem;
  background: linear-gradient(45deg, #ffffff, #9d4edd, #c77dff);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  text-shadow: 0 0 30px rgba(157, 78, 221, 0.5);
  animation: glow 3s ease-in-out infinite alternate;
}

@keyframes glow {
  from {
    filter: drop-shadow(0 0 10px rgba(157, 78, 221, 0.3));
  }
  to {
    filter: drop-shadow(0 0 20px rgba(157, 78, 221, 0.6));
  }
}

.subtitle {
  font-size: 1.3rem;
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.6;
}

/* QR Section */
.qr-section {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.qr-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  /* border: 1px solid red; */
  gap: 4rem;
  align-items: center;
}

.qr-wrapper {
  display: flex;
  flex-direction: column;
  /* border: 1px solid red; */
  align-items: center;
  gap: 4.5rem;
}

.qr-frame {
  position: relative;
  padding: 2rem;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 24px;
  box-shadow: 
    0 20px 60px rgba(157, 78, 221, 0.3),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
  border: 2px solid rgba(157, 78, 221, 0.2);
}

.qr-corners {
  position: absolute;
  inset: 1rem;
  pointer-events: none;
}

.corner {
  position: absolute;
  width: 30px;
  height: 30px;
  border: 3px solid #9d4edd;
}

.corner.top-left {
  top: 0;
  left: 0;
  border-right: none;
  border-bottom: none;
  border-top-left-radius: 8px;
}

.corner.top-right {
  top: 0;
  right: 0;
  border-left: none;
  border-bottom: none;
  border-top-right-radius: 8px;
}

.corner.bottom-left {
  bottom: 0;
  left: 0;
  border-right: none;
  border-top: none;
  border-bottom-left-radius: 8px;
}

.corner.bottom-right {
  bottom: 0;
  right: 0;
  border-left: none;
  border-top: none;
  border-bottom-right-radius: 8px;
}

.qr-canvas {
  border-radius: 12px;
  position: relative;
  z-index: 2;
}

.scan-line {
  position: absolute;
  top: 2rem;
  left: 2rem;
  right: 2rem;
  height: 2px;
  background: linear-gradient(90deg, transparent, #9d4edd, transparent);
  border-radius: 2px;
  opacity: 0;
  z-index: 3;
}

.scan-line.scanning {
  animation: scan 2s ease-in-out;
}

@keyframes scan {
  0% {
    transform: translateY(0);
    opacity: 0;
  }
  50% {
    opacity: 1;
  }
  100% {
    transform: translateY(280px);
    opacity: 0;
  }
}

.select-option {
  background: rgba(157, 78, 221, 0.1);
  border: 1px solid rgba(157, 78, 221, 0.3);
  border-radius: 12px;
  padding: 0.75rem 1rem;
  color: #ffffff;
  font-size: 0.9rem;
  min-width: 150px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.select-option:hover {
  background: rgba(157, 78, 221, 0.2);
  border-color: rgba(157, 78, 221, 0.5);
}

.select-option:focus {
  outline: none;
  border-color: #9d4edd;
  box-shadow: 0 0 0 3px rgba(157, 78, 221, 0.1);
}

.select-option option {
  background: #1a0825;
  color: #ffffff;
}


.qr-info {
  text-align: center;
  background: rgba(157, 78, 221, 0.1);
  padding: 1.5rem;
  border-radius: 16px;
  border: 1px solid rgba(157, 78, 221, 0.3);
  backdrop-filter: blur(10px);
}

.qr-info h3 {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
  color: #c77dff;
}

.session-time {
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.8);
  margin-bottom: 0.5rem;
}

.session-id {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.6);
  font-family: 'Courier New', monospace;
}

/* Instructions */
.instructions {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.instruction-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.5rem;
  background: rgba(157, 78, 221, 0.1);
  border-radius: 16px;
  border: 1px solid rgba(157, 78, 221, 0.3);
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.instruction-item:hover {
  background: rgba(157, 78, 221, 0.15);
  transform: translateX(5px);
}

.step-number {
  width: 40px;
  height: 40px;
  background: linear-gradient(45deg, #9d4edd, #c77dff);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: white;
  flex-shrink: 0;
}

.step-content h4 {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #ffffff;
}

.step-content p {
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.5;
}

/* Status section */
.status-section {
  max-width: 1200px;
  margin: 4rem auto 0;
  padding: 0 2rem;
}

.status-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.status-card {
  background: rgba(157, 78, 221, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(157, 78, 221, 0.3);
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s ease;
}

.status-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 30px rgba(157, 78, 221, 0.2);
  background: rgba(157, 78, 221, 0.15);
}

.status-icon {
  width: 50px;
  height: 50px;
  background: linear-gradient(45deg, #9d4edd, #c77dff);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.status-content h3 {
  font-size: 2rem;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: 0.25rem;
}

.status-content p {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
}

/* Activity section */
.activity-section {
  max-width: 1200px;
  margin: 4rem auto 0;
  padding: 0 2rem;
}

.activity-section h2 {
  font-size: 2.5rem;
  font-weight: bold;
  margin-bottom: 2rem;
  text-align: center;
  background: linear-gradient(45deg, #ffffff, #9d4edd);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  background: rgba(157, 78, 221, 0.1);
  border-radius: 16px;
  border: 1px solid rgba(157, 78, 221, 0.3);
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.activity-item:hover {
  background: rgba(157, 78, 221, 0.15);
  transform: translateX(5px);
}

.activity-avatar {
  width: 50px;
  height: 50px;
  background: linear-gradient(45deg, #9d4edd, #c77dff);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: white;
  flex-shrink: 0;
}

.activity-content {
  flex: 1;
}

.activity-content h4 {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
  color: #ffffff;
}

.activity-content p {
  color: rgba(255, 255, 255, 0.8);
  margin-bottom: 0.25rem;
}

.activity-time {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.6);
}

.activity-status {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 500;
}

.activity-status.present {
  background: rgba(34, 197, 94, 0.2);
  color: #22c55e;
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.activity-status.late {
  background: rgba(251, 191, 36, 0.2);
  color: #fbbf24;
  border: 1px solid rgba(251, 191, 36, 0.3);
}

.activity-status.absent {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: currentColor;
}

/* Footer */
.footer {
  background: rgba(10, 10, 10, 0.8);
  padding: 2rem;
  text-align: center;
  border-top: 1px solid rgba(157, 78, 221, 0.3);
  margin-top: 4rem;
}

/* Responsive design */
@media (max-width: 1024px) {
  .qr-container {
    grid-template-columns: 1fr;
    gap: 2rem;
  }
}

@media (max-width: 768px) {
  .header-section h1 {
    font-size: 2.5rem;
  }
  
  .subtitle {
    font-size: 1.1rem;
  }
  
  .activity-section h2 {
    font-size: 2rem;
  }
  
  .qr-frame {
    padding: 1rem;
  }

  .qr-corners {
    inset: 0.6rem;
  }
  
  .status-grid {
    grid-template-columns: 1fr;
  }
  
  .main-content {
    padding-top: 80px;
  }
}

/* Loading animation */
.fade-in {
  animation: fadeIn 1s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>