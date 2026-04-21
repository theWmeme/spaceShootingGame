import {type ChangeEvent, useState} from 'react'
import {useEffect} from "react";
import './addEnemies.css'

const SKINS = ['kitty alt skin1', 'kitty alt skin2', 'kitty alt skin3'];

const skinImages: Record<string, string> = {
  'kitty alt skin1': new URL('./images/kitty alt skin1.png', import.meta.url).href,
  'kitty alt skin2': new URL('./images/kitty alt skin2.png', import.meta.url).href,
  'kitty alt skin3': new URL('./images/kitty alt skin3.png', import.meta.url).href,
};

function AddEnemies() {
    const [data, setData] = useState<string[]>([]);
    const [val, setVal] = useState<string>('');
    const [updateVal, setUpdateVal] = useState<string>('');
    const [nameSkinPairs, setNameSkinPairs] = useState<{name: string, skin: string}[]>([]);
    const API_URL = 'https://encrypted-server-ixzt.onrender.com';

    useEffect(() => {
        fetch(API_URL)
            .then(res => res.json())
            .then(json => setData(json.data));
    }, []);

    useEffect(() => {
        setNameSkinPairs(data.map(name => ({
            name,
            skin: SKINS[Math.floor(Math.random() * SKINS.length)]
        })));
    }, [data]);


    const handleInputChange = (e: ChangeEvent<HTMLInputElement>) => setVal(e.target.value);
    const handleInputChange2 = (e: ChangeEvent<HTMLInputElement>) => setUpdateVal(e.target.value);


    const handlePost = async () => {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ newName: val })
        });
        const result = await response.json();
        setData(result.names);
        setVal('');
    };



    const handlePut = async () => {
        const response = await fetch(API_URL, {
            method: 'PUT',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ oldName: updateVal, updatedName: val })
        });
        const result = await response.json();
        setData(result.data);
    };


    const handleDelete = async () => {
        const response = await fetch(`${API_URL}/${val}`, { method: 'DELETE' });
        const result = await response.json();
        setData(result.remaining);
    };

    return (
        <div className="add-players-container">
            <div className="main-content">
                <h3 className="title">Names Of Incoming Enemies</h3>
                
                <div className="names-list">
                    {nameSkinPairs.map(({name, skin}) => (
                        <div key={name} className="name-entry">
                            <span className="player-name">{name}</span>
                            <img src={skinImages[skin]} alt={skin} className="skin-image" />
                        </div>
                    ))}
                </div>

                <div className="input-section">
                    <input 
                        type="text" 
                        value={val} 
                        onChange={handleInputChange} 
                        placeholder={'New Name'}
                        className="styled-input"
                    />
                    <input 
                        type="text" 
                        value={updateVal} 
                        onChange={handleInputChange2} 
                        placeholder={'Current Name (to update)'}
                        className="styled-input"
                    />
                </div>
            </div>

            <div className="button-section">
                <button onClick={handlePost} className="styled-button">Add Name</button>
                <button onClick={handlePut} className="styled-button">Update a Name</button>
                <button onClick={handleDelete} className="styled-button">Delete Name</button>
            </div>

            <div className="instructions">
                <p>-To add a name, type the name you want to add in the box.</p>
                <p>-To update a name, type the name you want to update in the box.</p>
                <p>-Reload the page to see the changes.</p>
            </div>
        </div>
    );
}

export default AddEnemies
