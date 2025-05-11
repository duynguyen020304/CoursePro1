# %%
import os
import numpy as np
import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns

# %%
data = pd.read_csv("../input/coursera-courses-dataset-2021/Coursera.csv")
data.to_csv("Coursera.txt", sep='\t', index=False)
data.head(5)

# %%
data = data[['Course Name','Difficulty Level','Course Description','Skills']]
data.head(5)

# %%
data['Course Name'] = data['Course Name'].str.replace(' ','',regex=False)
data['Course Name'] = data['Course Name'].str.replace(',,','',regex=False)
data['Course Name'] = data['Course Name'].str.replace(':','',regex=False)
data['Course Description'] = data['Course Description'].str.replace(' ','',regex=False)
data['Course Description'] = data['Course Description'].str.replace(',,','',regex=False)
data['Course Description'] = data['Course Description'].str.replace('_','',regex=False)
data['Course Description'] = data['Course Description'].str.replace(':','',regex=False)
data['Course Description'] = data['Course Description'].str.replace('(','',regex=False)
data['Course Description'] = data['Course Description'].str.replace(')','',regex=False)

#removing paranthesis from skills columns 
data['Skills'] = data['Skills'].str.replace('(','',regex=False)
data['Skills'] = data['Skills'].str.replace(')','',regex=False)
data.head(5)

# %%
data['tags'] = data['Course Name'] + data['Difficulty Level'] + data['Course Description'] + data['Skills']

# %%
data.head(5)

# %%
data['tags'].iloc[1]

# %%
new_df = data[['Course Name','tags']].copy()
new_df['tags'] = data['tags'].str.replace(',',' ',regex=False)
new_df['Course Name'] = data['Course Name'].str.replace(',',' ')
new_df = new_df.rename(columns = {'Course Name':'course_name'})
new_df['tags'] = new_df['tags'].apply(lambda x:x.lower())
new_df.head(5)

# %%
import nltk
from nltk.stem.porter import PorterStemmer
ps = PorterStemmer()
def stem(text):
    y=[]
    for i in text.split():
        y.append(ps.stem(i))
    return " ".join(y)
new_df['tags'] = new_df['tags'].apply(stem) #applying stemming on the tags column

# %%
from sklearn.feature_extraction.text import CountVectorizer
cv = CountVectorizer(max_features=5000,stop_words='english')
vectors = cv.fit_transform(new_df['tags']).toarray()

# %%
from sklearn.metrics.pairwise import cosine_similarity
similarity = cosine_similarity(vectors)

# %%
def recommend(course):
    course_index = new_df[new_df['course_name'] == course].index[0]
    distances = similarity[course_index]
    course_list = sorted(list(enumerate(distances)),reverse=True, key=lambda x:x[1])[1:7]
    
    for i in course_list:
        print(new_df.iloc[i[0]].course_name)

# %%
print(new_df['course_name'][0])

# %%
course_name = new_df['course_name'][0]
recommend(f'{course_name}') 


