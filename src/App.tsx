import { useState } from 'react';
import AddEnemies from './addEnemies';
import homepageImage from './images/homepage image.png';
import './App.css';

function App() {
  const [currentPage, setCurrentPage] = useState<'home' | 'addPlayers'>('home');

  if (currentPage === 'addPlayers') {
    return <AddEnemies />;
  }

  return (
    <div className="homepage" style={{ backgroundImage: `url(${homepageImage})` }}>
      <button
        onClick={() => setCurrentPage('addPlayers')}
        className="start-button"
      >
        Start!
      </button>
    </div>
  );
}

export default App;

