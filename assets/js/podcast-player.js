class PodcastPlayer {
    constructor() {
        this.currentlyPlaying = null;
        this.isPlaying = false;
        this.audioElement = null;
        this.podcasts = {};
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadPodcastData();
    }

    loadPodcastData() {
        // Simulation des données de podcasts (remplacez par un appel AJAX vers votre contrôleur Symfony)
        this.podcasts = {
            '1': {
                id: '1',
                title: "L'Art de la Productivité",
                duration: 45,
                coverGradient: 'from-purple-600',
                audioUrl: '/audio/productivity.mp3' // URL fictive
            },
            '2': {
                id: '2',
                title: "Intelligence Artificielle : L'Avenir",
                duration: 32,
                coverGradient: 'from-cyan-500',
                audioUrl: '/audio/ai-future.mp3'
            },
            '3': {
                id: '3',
                title: "Méditation & Bien-être",
                duration: 28,
                coverGradient: 'from-green-500',
                audioUrl: '/audio/meditation.mp3'
            }
        };
    }

    setupEventListeners() {
        // Boutons play/pause des podcasts
        document.addEventListener('click', (e) => {
            if (e.target.closest('[data-action="play-pause"]')) {
                const button = e.target.closest('[data-action="play-pause"]');
                const podcastId = button.dataset.podcastId;
                this.handlePlayPause(podcastId);
            }
        });

        // Contrôles du lecteur audio
        document.addEventListener('DOMContentLoaded', () => {
            const playPauseMain = document.getElementById('play-pause-main');
            const skipBack = document.getElementById('skip-back');
            const skipForward = document.getElementById('skip-forward');
            const progressBar = document.getElementById('progress-bar');
            const volumeBar = document.getElementById('volume-bar');
            const closePlayer = document.getElementById('close-player');

            if (playPauseMain) {
                playPauseMain.addEventListener('click', () => this.togglePlayPause());
            }
            
            if (skipBack) {
                skipBack.addEventListener('click', () => this.skipTime(-10));
            }
            
            if (skipForward) {
                skipForward.addEventListener('click', () => this.skipTime(10));
            }
            
            if (progressBar) {
                progressBar.addEventListener('input', (e) => this.handleSeek(e));
            }
            
            if (volumeBar) {
                volumeBar.addEventListener('input', (e) => this.handleVolumeChange(e));
            }
            
            if (closePlayer) {
                closePlayer.addEventListener('click', () => this.closePlayer());
            }
        });
    }

    handlePlayPause(podcastId) {
        if (this.currentlyPlaying === podcastId) {
            this.togglePlayPause();
        } else {
            this.playPodcast(podcastId);
        }
    }

    playPodcast(podcastId) {
        this.currentlyPlaying = podcastId;
        this.updatePlayButtons();
        this.showAudioPlayer(podcastId);
        this.loadAudio(podcastId);
    }

    togglePlayPause() {
        if (!this.audioElement) return;

        if (this.isPlaying) {
            this.audioElement.pause();
            this.isPlaying = false;
        } else {
            // Simulation de lecture avec URL factice
            this.simulateAudioPlayback();
            this.isPlaying = true;
        }
        
        this.updatePlayButtons();
    }

    simulateAudioPlayback() {
        // Simulation basique pour démonstration
        const currentTimeElement = document.getElementById('current-time');
        const progressBar = document.getElementById('progress-bar');
        
        if (currentTimeElement && progressBar) {
            let currentTime = 0;
            const duration = this.podcasts[this.currentlyPlaying]?.duration * 60 || 180;
            
            this.playbackInterval = setInterval(() => {
                if (this.isPlaying) {
                    currentTime += 1;
                    currentTimeElement.textContent = this.formatTime(currentTime);
                    progressBar.value = (currentTime / duration) * 100;
                    
                    if (currentTime >= duration) {
                        this.isPlaying = false;
                        this.updatePlayButtons();
                        clearInterval(this.playbackInterval);
                    }
                }
            }, 1000);
        }
    }

    loadAudio(podcastId) {
        const podcast = this.podcasts[podcastId];
        if (!podcast) return;

        // Simulation d'un élément audio
        this.audioElement = document.getElementById('audio-element');
        if (this.audioElement) {
            // Dans un vrai projet, vous chargeriez l'URL audio réelle
            // this.audioElement.src = podcast.audioUrl;
        }
    }

    showAudioPlayer(podcastId) {
        const podcast = this.podcasts[podcastId];
        if (!podcast) return;

        const audioPlayer = document.getElementById('audio-player');
        const playerTitle = document.getElementById('player-title');
        const playerCover = document.getElementById('player-cover');
        const totalTime = document.getElementById('total-time');

        if (audioPlayer) {
            audioPlayer.classList.remove('hidden');
        }
        
        if (playerTitle) {
            playerTitle.textContent = podcast.title;
        }
        
        if (playerCover) {
            playerCover.className = `player-cover ${podcast.coverGradient}`;
        }
        
        if (totalTime) {
            totalTime.textContent = this.formatTime(podcast.duration * 60);
        }
    }

    closePlayer() {
        const audioPlayer = document.getElementById('audio-player');
        if (audioPlayer) {
            audioPlayer.classList.add('hidden');
        }
        
        this.currentlyPlaying = null;
        this.isPlaying = false;
        
        if (this.playbackInterval) {
            clearInterval(this.playbackInterval);
        }
        
        this.updatePlayButtons();
    }

    updatePlayButtons() {
        // Mise à jour des icônes play/pause
        document.querySelectorAll('[data-action="play-pause"]').forEach(button => {
            const podcastId = button.dataset.podcastId;
            const playIcon = button.querySelector('.play-icon');
            const pauseIcon = button.querySelector('.pause-icon');
            
            if (this.currentlyPlaying === podcastId && this.isPlaying) {
                playIcon?.classList.add('hidden');
                pauseIcon?.classList.remove('hidden');
            } else {
                playIcon?.classList.remove('hidden');
                pauseIcon?.classList.add('hidden');
            }
        });

        // Mise à jour du bouton principal du lecteur
        const mainPlayIcon = document.querySelector('#play-pause-main .play-icon');
        const mainPauseIcon = document.querySelector('#play-pause-main .pause-icon');
        
        if (this.isPlaying) {
            mainPlayIcon?.classList.add('hidden');
            mainPauseIcon?.classList.remove('hidden');
        } else {
            mainPlayIcon?.classList.remove('hidden');
            mainPauseIcon?.classList.add('hidden');
        }
    }

    skipTime(seconds) {
        // Simulation du saut de temps
        console.log(`Saut de ${seconds} secondes`);
    }

    handleSeek(e) {
        // Simulation de la recherche dans la piste
        const value = e.target.value;
        console.log(`Recherche à ${value}%`);
    }

    handleVolumeChange(e) {
        const volume = e.target.value;
        if (this.audioElement) {
            this.audioElement.volume = volume;
        }
    }

    formatTime(time) {
        const minutes = Math.floor(time / 60);
        const seconds = Math.floor(time % 60);
        return `${minutes}:${seconds.toString().padStart(2, '0')}`;
    }
}

// Initialisation du lecteur de podcast
document.addEventListener('DOMContentLoaded', () => {
    new PodcastPlayer();
});