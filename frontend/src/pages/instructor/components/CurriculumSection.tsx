import { useState, useRef, useCallback, useMemo } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import toast from 'react-hot-toast';
import { createEditor, Editor, Transforms, Element as SlateElement, Text, Descendant } from 'slate';
import { Slate, Editable, withReact } from 'slate-react';

/* ================================================================
   Types (imported from CourseForm)
   ================================================================ */

export interface ResourceDraft {
  _id: string;
  file: File;
  title: string;
  sort_order: number;
}

export interface LessonDraft {
  _id: string;
  title: string;
  content: string;
  sort_order: number;
  isExpanded: boolean;
  videoFile: File | null;
  resources: ResourceDraft[];
}

export interface ChapterDraft {
  _id: string;
  title: string;
  description: string;
  sort_order: number;
  isExpanded: boolean;
  lessons: LessonDraft[];
}

export type FormAction =
  | { type: 'ADD_CHAPTER' }
  | { type: 'REMOVE_CHAPTER'; index: number }
  | { type: 'UPDATE_CHAPTER'; index: number; field: 'title' | 'description'; value: string }
  | { type: 'TOGGLE_CHAPTER'; index: number }
  | { type: 'ADD_LESSON'; chapterIndex: number }
  | { type: 'REMOVE_LESSON'; chapterIndex: number; lessonIndex: number }
  | { type: 'UPDATE_LESSON'; chapterIndex: number; lessonIndex: number; field: 'title' | 'content'; value: string }
  | { type: 'TOGGLE_LESSON'; chapterIndex: number; lessonIndex: number }
  | { type: 'SET_VIDEO_FILE'; chapterIndex: number; lessonIndex: number; file: File | null }
  | { type: 'ADD_RESOURCE'; chapterIndex: number; lessonIndex: number; file: File }
  | { type: 'REMOVE_RESOURCE'; chapterIndex: number; lessonIndex: number; resourceIndex: number };

interface CurriculumSectionProps {
  chapters: ChapterDraft[];
  dispatch: React.Dispatch<FormAction>;
  mode: 'create' | 'edit';
  selectedChapterIndex: number | null;
  onSelectChapter: (index: number | null) => void;
}

/* ================================================================
   Constants & Helpers
   ================================================================ */

const VALID_VIDEO_TYPES = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'];
const MAX_VIDEO_SIZE = 500 * 1024 * 1024;

const SLATE_EMPTY: Descendant[] = [{ type: 'paragraph', children: [{ text: '' }] } as Descendant];

function createSlateEmpty(): Descendant[] {
  return [{ type: 'paragraph', children: [{ text: '' }] } as Descendant];
}

function formatFileSize(bytes: number): string {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function escapeHtml(text: string): string {
  return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function serializeSlateToHtml(nodes: Descendant[]): string {
  return nodes.map(serializeNode).join('');
}

function serializeNode(node: Descendant): string {
  if (Text.isText(node)) {
    let text = escapeHtml(node.text);
    const marks = node as unknown as Record<string, unknown>;
    if (marks.bold) text = `<strong>${text}</strong>`;
    if (marks.italic) text = `<em>${text}</em>`;
    if (marks.underline) text = `<u>${text}</u>`;
    return text;
  }
  const element = node as unknown as { children: Descendant[]; type?: string };
  const children = element.children.map(serializeNode).join('');
  switch (element.type) {
    case 'heading-one': return `<h1>${children}</h1>`;
    case 'heading-two': return `<h2>${children}</h2>`;
    case 'heading-three': return `<h3>${children}</h3>`;
    case 'block-quote': return `<blockquote><p>${children}</p></blockquote>`;
    case 'numbered-list': return `<ol>${children}</ol>`;
    case 'bulleted-list': return `<ul>${children}</ul>`;
    case 'list-item': return `<li>${children}</li>`;
    case 'link':
      const linkNode = node as unknown as Record<string, unknown>;
      return `<a href="${escapeHtml(String(linkNode.url))}">${children}</a>`;
    default: return `<p>${children}</p>`;
  }
}

function isMarkActive(editor: Editor, format: string): boolean {
  const marks = Editor.marks(editor) as Record<string, unknown> | null;
  return marks ? Boolean(marks[format]) : false;
}

function toggleMark(editor: Editor, format: string): void {
  const isActive = isMarkActive(editor, format);
  if (isActive) Editor.removeMark(editor, format);
  else Editor.addMark(editor, format, true);
}

const LIST_TYPES = ['numbered-list', 'bulleted-list'];

function isBlockActive(editor: Editor, format: string): boolean {
  const { selection } = editor;
  if (!selection) return false;
  const [match] = Array.from(Editor.nodes(editor, {
    at: Editor.unhangRange(editor, selection),
    match: (n) => {
      if (!SlateElement.isElement(n)) return false;
      const element = n as unknown as Record<string, unknown>;
      return element.type === format;
    },
  }));
  return !!match;
}

function toggleBlock(editor: Editor, format: string): void {
  const isActive = isBlockActive(editor, format);
  const isList = LIST_TYPES.includes(format);
  Transforms.unwrapNodes(editor, {
    match: (n) => {
      if (!SlateElement.isElement(n)) return false;
      const element = n as unknown as Record<string, unknown>;
      const type = element.type as string | undefined;
      return type !== undefined && LIST_TYPES.includes(type);
    },
    split: true
  });
  Transforms.setNodes(editor, { type: isActive ? 'paragraph' : isList ? 'list-item' : format } as Partial<SlateElement>);
  if (!isActive && isList) {
    const block = { type: format as string, children: [] } as unknown as SlateElement;
    Transforms.wrapNodes(editor, block);
  }
}

/* ================================================================
   Sub-components
   ================================================================ */

function ChevronIcon({ expanded }: { expanded: boolean }) {
  return (
    <motion.svg
      animate={{ rotate: expanded ? 90 : 0 }}
      transition={{ duration: 0.2 }}
      className="w-4 h-4 text-slate-400 shrink-0"
      viewBox="0 0 20 20"
      fill="currentColor"
    >
      <path fillRule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clipRule="evenodd" />
    </motion.svg>
  );
}

function VideoUploadZone({
  videoFile,
  onFileSelect,
  onRemove,
}: {
  videoFile: File | null;
  onFileSelect: (file: File) => void;
  onRemove: () => void;
}) {
  const [isDragging, setIsDragging] = useState(false);
  const inputRef = useRef<HTMLInputElement>(null);

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(false);
    const file = e.dataTransfer.files[0];
    if (file) onFileSelect(file);
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) onFileSelect(file);
  };

  const handleSelect = (file: File) => {
    if (!VALID_VIDEO_TYPES.includes(file.type)) {
      toast.error('Invalid video format. Use MP4, WebM, or OGG.');
      return;
    }
    if (file.size > MAX_VIDEO_SIZE) {
      toast.error('Video must be under 500MB.');
      return;
    }
    onFileSelect(file);
  };

  return (
    <div
      onDragOver={(e) => { e.preventDefault(); setIsDragging(true); }}
      onDragLeave={() => setIsDragging(false)}
      onDrop={handleDrop}
      onClick={() => inputRef.current?.click()}
      className={`border-2 border-dashed rounded-xl p-6 text-center cursor-pointer transition-all duration-200 ${
        isDragging
          ? 'border-indigo-500 bg-indigo-50/70'
          : videoFile
            ? 'border-emerald-400 bg-emerald-50/50'
            : 'border-slate-300 hover:border-indigo-400 hover:bg-slate-50'
      }`}
    >
      <input ref={inputRef} type="file" accept={VALID_VIDEO_TYPES.join(',')} className="hidden" onChange={handleChange} />
      {videoFile ? (
        <div className="flex items-center justify-center gap-3">
          <div className="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
            <svg className="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <div className="text-left">
            <p className="text-sm font-medium text-slate-700">{videoFile.name}</p>
            <p className="text-xs text-slate-500">{formatFileSize(videoFile.size)}</p>
          </div>
          <button
            onClick={(e) => { e.stopPropagation(); onRemove(); }}
            className="ml-2 text-red-500 hover:text-red-700 text-xs font-medium px-2 py-1 rounded hover:bg-red-50 transition-colors"
          >
            Remove
          </button>
        </div>
      ) : (
        <div>
          <div className="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center mx-auto mb-3">
            <svg className="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
              <path strokeLinecap="round" strokeLinejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
            </svg>
          </div>
          <p className="text-slate-600 text-sm">Drop your video here or <span className="text-indigo-600 font-medium underline underline-offset-2">browse</span></p>
          <p className="text-xs text-slate-400 mt-1">MP4, WebM, OGG &middot; Max 500MB</p>
        </div>
      )}
    </div>
  );
}

function ToolbarButton({ active, onMouseDown, children }: { active: boolean; onMouseDown: (e: React.MouseEvent) => void; children: React.ReactNode }) {
  return (
    <button
      type="button"
      onMouseDown={onMouseDown}
      className={`w-7 h-7 rounded flex items-center justify-center text-xs font-bold transition-colors ${
        active ? 'bg-indigo-100 text-indigo-700' : 'text-slate-500 hover:bg-slate-100'
      }`}
    >
      {children}
    </button>
  );
}

function RichTextEditor({ onChange }: { onChange: (html: string) => void }) {
  const editor = useMemo(() => withReact(createEditor()), []);
  const handleChange = useCallback(
    (newValue: Descendant[]) => {
      const isAstChange = editor.operations.some((op) => op.type !== 'set_selection');
      if (isAstChange) {
        onChange(serializeSlateToHtml(newValue));
      }
    },
    [editor, onChange],
  );

  const renderElement = useCallback((props: any) => {
    const { attributes, children, element } = props;
    const elementType = (element as any).type;
    switch (elementType) {
      case 'heading-one': return <h1 {...attributes} className="text-lg font-bold text-slate-900">{children}</h1>;
      case 'heading-two': return <h2 {...attributes} className="text-base font-bold text-slate-900">{children}</h2>;
      case 'heading-three': return <h3 {...attributes} className="text-sm font-bold text-slate-900">{children}</h3>;
      case 'block-quote': return <blockquote {...attributes} className="border-l-2 border-indigo-300 pl-3 italic text-slate-600">{children}</blockquote>;
      case 'numbered-list': return <ol {...attributes} className="list-decimal pl-5">{children}</ol>;
      case 'bulleted-list': return <ul {...attributes} className="list-disc pl-5">{children}</ul>;
      case 'list-item': return <li {...attributes}>{children}</li>;
      case 'link': return <a {...attributes} href={String((element as any).url)} className="text-indigo-600 underline">{children}</a>;
      default: return <p {...attributes}>{children}</p>;
    }
  }, []);

  const renderLeaf = useCallback((props: any) => {
    const { attributes, children, leaf } = props;
    let el = <span {...attributes}>{children}</span>;
    if (leaf.bold) el = <strong {...attributes}>{children}</strong>;
    if (leaf.italic) el = <em {...attributes}>{children}</em>;
    if (leaf.underline) el = <u {...attributes}>{children}</u>;
    return el;
  }, []);

  return (
    <div className="border border-slate-300 rounded-lg overflow-hidden bg-white">
      <div className="flex items-center gap-0.5 px-2 py-1.5 border-b border-slate-200 bg-slate-50/50">
        <ToolbarButton active={isMarkActive(editor, 'bold')} onMouseDown={(e) => { e.preventDefault(); toggleMark(editor, 'bold'); }}>
          <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M15.6 10.794c.124-.312.2-.724.2-1.094 0-1.74-1.41-3.2-3.2-3.2H7.6v12h5.6c1.74 0 3.2-1.46 3.2-3.2 0-1.28-.84-2.36-2-2.806zm-4.4-1.994h1.6c.44 0 .8.36.8.8s-.36.8-.8.8H11.2V8.8zm2 6.4h-2v-1.6h2c.44 0 .8.36.8.8s-.36.8-.8.8z"/></svg>
        </ToolbarButton>
        <ToolbarButton active={isMarkActive(editor, 'italic')} onMouseDown={(e) => { e.preventDefault(); toggleMark(editor, 'italic'); }}>
          <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M10 4v3h2.3l-3.3 10H6v3h8v-3h-2.3l3.3-10H18V4z"/></svg>
        </ToolbarButton>
        <ToolbarButton active={isMarkActive(editor, 'underline')} onMouseDown={(e) => { e.preventDefault(); toggleMark(editor, 'underline'); }}>
          <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17c3.31 0 6-2.69 6-6V3h-2.5v8c0 1.93-1.57 3.5-3.5 3.5S8.5 12.93 8.5 11V3H6v8c0 3.31 2.69 6 6 6zm-7 2v2h14v-2H5z"/></svg>
        </ToolbarButton>
        <div className="w-px h-5 bg-slate-300 mx-1" />
        <ToolbarButton active={isBlockActive(editor, 'heading-two')} onMouseDown={(e) => { e.preventDefault(); toggleBlock(editor, 'heading-two'); }}>
          <span className="text-[10px]">H2</span>
        </ToolbarButton>
        <ToolbarButton active={isBlockActive(editor, 'heading-three')} onMouseDown={(e) => { e.preventDefault(); toggleBlock(editor, 'heading-three'); }}>
          <span className="text-[10px]">H3</span>
        </ToolbarButton>
        <div className="w-px h-5 bg-slate-300 mx-1" />
        <ToolbarButton active={isBlockActive(editor, 'bulleted-list')} onMouseDown={(e) => { e.preventDefault(); toggleBlock(editor, 'bulleted-list'); }}>
          <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M4 10.5c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zm0-6c-.83 0-1.5.67-1.5 1.5S3.17 7.5 4 7.5 5.5 6.83 5.5 6 4.83 4.5 4 4.5zm0 12c-.83 0-1.5.68-1.5 1.5s.68 1.5 1.5 1.5 1.5-.68 1.5-1.5-.67-1.5-1.5-1.5zM7 19h14v-2H7v2zm0-6h14v-2H7v2zm0-8v2h14V5H7z"/></svg>
        </ToolbarButton>
        <ToolbarButton active={isBlockActive(editor, 'numbered-list')} onMouseDown={(e) => { e.preventDefault(); toggleBlock(editor, 'numbered-list'); }}>
          <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M2 17h2v.5H3v1h1v.5H2v1h3v-4H2v1zm1-9h1V4H2v1h1v3zm-1 3h1.8L2 13.1v.9h3v-1H3.2L5 10.9V10H2v1zm5-6v2h14V5H7zm0 14h14v-2H7v2zm0-6h14v-2H7v2z"/></svg>
        </ToolbarButton>
        <ToolbarButton active={isBlockActive(editor, 'block-quote')} onMouseDown={(e) => { e.preventDefault(); toggleBlock(editor, 'block-quote'); }}>
          <svg className="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"/></svg>
        </ToolbarButton>
      </div>
      <Slate editor={editor} initialValue={createSlateEmpty()} onChange={handleChange}>
        <Editable
          renderElement={renderElement}
          renderLeaf={renderLeaf}
          placeholder="Write your lesson content here..."
          className="px-4 py-3 min-h-[140px] text-sm text-slate-700 focus:outline-none prose prose-slate prose-sm max-w-none"
          spellCheck
        />
      </Slate>
    </div>
  );
}

/* ================================================================
   Chapter Sidebar (Left Column)
   ================================================================ */

function ChapterSidebar({
  chapters,
  selectedChapterIndex,
  selectedLessonIndex,
  onSelectChapter,
  onSelectLesson,
  onAddChapter,
  onRemoveChapter,
  onAddLesson,
  onRemoveLesson,
}: {
  chapters: ChapterDraft[];
  selectedChapterIndex: number | null;
  selectedLessonIndex: number | null;
  onSelectChapter: (index: number) => void;
  onSelectLesson: (chapterIndex: number, lessonIndex: number) => void;
  onAddChapter: () => void;
  onRemoveChapter: (index: number) => void;
  onAddLesson: (chapterIndex: number) => void;
  onRemoveLesson: (chapterIndex: number, lessonIndex: number) => void;
}) {
  return (
    <div className="bg-white rounded-xl border border-slate-200 overflow-hidden flex flex-col h-full">
      <div className="px-3 py-3 border-b border-slate-100">
        <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Chapters</span>
      </div>

      <div className="flex-1 overflow-y-auto px-2 py-2 min-h-0">
        {chapters.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-12 text-center">
            <div className="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mb-3">
              <svg className="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
              </svg>
            </div>
            <p className="text-sm text-slate-500 font-medium">No chapters yet</p>
            <p className="text-xs text-slate-400 mt-1">Add your first chapter</p>
          </div>
        ) : (
          <AnimatePresence initial={false}>
            {chapters.map((chapter, ci) => {
              const isChapterActive = selectedChapterIndex === ci;
              return (
                <div key={chapter._id} className="mb-1">
                  <motion.div
                    initial={{ opacity: 0, x: -12 }}
                    animate={{ opacity: 1, x: 0 }}
                    exit={{ opacity: 0, x: -12, height: 0 }}
                    transition={{ duration: 0.2 }}
                    onClick={() => onSelectChapter(ci)}
                    className={`group relative flex items-start gap-2.5 px-3 py-2.5 rounded-lg cursor-pointer transition-colors ${
                      isChapterActive && selectedLessonIndex === null
                        ? 'bg-indigo-50'
                        : 'hover:bg-slate-50'
                    }`}
                  >
                    {isChapterActive && selectedLessonIndex === null && (
                      <motion.div
                        layoutId="chapter-active-indicator"
                        className="absolute left-0 top-1 bottom-1 w-[3px] bg-indigo-500 rounded-full"
                        transition={{ type: "spring", stiffness: 500, damping: 35 }}
                      />
                    )}
                    <div className="flex-1 min-w-0 pl-1">
                      <div className="flex items-center gap-2">
                        <span className={`text-[11px] font-semibold px-1.5 py-0.5 rounded shrink-0 ${
                          isChapterActive
                            ? 'text-indigo-700 bg-indigo-100'
                            : 'text-slate-500 bg-slate-100'
                        }`}>
                          {ci + 1}
                        </span>
                        <span className="text-sm font-medium text-slate-800 truncate">
                          {chapter.title || 'Untitled Chapter'}
                        </span>
                      </div>
                    </div>
                    <button
                      type="button"
                      onClick={(e) => { e.stopPropagation(); onRemoveChapter(ci); }}
                      className="shrink-0 opacity-0 group-hover:opacity-100 text-slate-400 hover:text-rose-500 transition-all p-1 rounded hover:bg-rose-50 mt-0.5"
                    >
                      <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                        <path strokeLinecap="round" strokeLinejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                      </svg>
                    </button>
                  </motion.div>

                  {/* Nested lessons */}
                  {isChapterActive && (
                    <div className="ml-4 mt-1">
                      <AnimatePresence initial={false}>
                        {chapter.lessons.map((lesson, li) => (
                          <motion.div
                            key={lesson._id}
                            initial={{ opacity: 0, x: -8 }}
                            animate={{ opacity: 1, x: 0 }}
                            exit={{ opacity: 0, x: -8, height: 0 }}
                            transition={{ duration: 0.15 }}
                            onClick={() => onSelectLesson(ci, li)}
                            className={`group/lesson relative flex items-center gap-2 px-3 py-2 rounded-lg cursor-pointer transition-colors mb-0.5 ${
                              isChapterActive && selectedLessonIndex === li
                                ? 'bg-indigo-50'
                                : 'hover:bg-slate-50'
                            }`}
                          >
                            {isChapterActive && selectedLessonIndex === li && (
                              <motion.div
                                layoutId="lesson-active-indicator"
                                className="absolute left-0 top-1 bottom-1 w-[3px] bg-indigo-400 rounded-full"
                                transition={{ type: "spring", stiffness: 500, damping: 35 }}
                              />
                            )}
                            <span className={`text-[10px] font-mono shrink-0 ${
                              isChapterActive && selectedLessonIndex === li
                                ? 'text-indigo-600'
                                : 'text-slate-400'
                            }`}>
                              {ci + 1}.{li + 1}
                            </span>
                            <span className="text-sm text-slate-700 truncate flex-1">
                              {lesson.title || 'Untitled Lesson'}
                            </span>
                            {lesson.videoFile && (
                              <svg className="w-3 h-3 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z" />
                              </svg>
                            )}
                            <button
                              type="button"
                              onClick={(e) => { e.stopPropagation(); onRemoveLesson(ci, li); }}
                              className="shrink-0 opacity-0 group-hover/lesson:opacity-100 text-slate-400 hover:text-rose-500 transition-all p-0.5 rounded hover:bg-rose-50"
                            >
                              <svg className="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                              </svg>
                            </button>
                          </motion.div>
                        ))}
                      </AnimatePresence>
                      <button
                        type="button"
                        onClick={() => onAddLesson(ci)}
                        className="w-full flex items-center justify-center gap-1 py-1.5 text-[11px] text-slate-400 hover:text-indigo-600 rounded-lg hover:bg-indigo-50/50 transition-colors mt-0.5"
                      >
                        <svg className="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                          <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Add Lesson
                      </button>
                    </div>
                  )}
                </div>
              );
            })}
          </AnimatePresence>
        )}
      </div>

      <div className="px-3 py-3 border-t border-slate-100">
        <button
          type="button"
          onClick={onAddChapter}
          className="w-full py-2 border-2 border-dashed border-slate-300 rounded-lg text-sm text-slate-500 font-medium hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50/30 transition-all flex items-center justify-center gap-1.5"
        >
          <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
          Add Chapter
        </button>
      </div>
    </div>
  );
}

/* ================================================================
   Lesson Detail Panel (Right Column — single lesson)
   ================================================================ */

function LessonDetailPanel({
  lesson,
  chapterIndex,
  lessonIndex,
  dispatch,
  mode,
}: {
  lesson: LessonDraft;
  chapterIndex: number;
  lessonIndex: number;
  dispatch: React.Dispatch<FormAction>;
  mode: 'create' | 'edit';
}) {
  return (
    <div className="bg-white rounded-xl border border-slate-200 overflow-hidden flex flex-col h-full">
      <div className="flex-1 overflow-y-auto px-6 py-5">
        {/* Lesson header */}
        <div className="border-b border-slate-200 pb-4 mb-5">
          <div className="flex items-center justify-between">
            <span className="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">
              {chapterIndex + 1}.{lessonIndex + 1}
            </span>
            <button
              type="button"
              onClick={() => dispatch({ type: 'REMOVE_LESSON', chapterIndex, lessonIndex })}
              className="text-sm text-slate-400 hover:text-rose-500 transition-colors px-2 py-1 rounded hover:bg-rose-50"
            >
              Delete Lesson
            </button>
          </div>
          <input
            type="text"
            value={lesson.title}
            onChange={(e) => dispatch({ type: 'UPDATE_LESSON', chapterIndex, lessonIndex, field: 'title', value: e.target.value })}
            placeholder="Lesson Title"
            className="w-full text-lg font-sora font-semibold text-slate-900 placeholder:text-slate-400 focus:outline-none bg-transparent mt-2"
          />
        </div>

        {/* Lesson content editor */}
        <div className="space-y-5">
          <div>
            <label className="block text-xs font-medium text-slate-600 mb-1.5">Lesson Content</label>
            <RichTextEditor
              onChange={(html) => dispatch({ type: 'UPDATE_LESSON', chapterIndex, lessonIndex, field: 'content', value: html })}
            />
          </div>

          {/* Video upload */}
          {mode === 'create' && (
            <div>
              <label className="block text-xs font-medium text-slate-600 mb-1.5">Video</label>
              <VideoUploadZone
                videoFile={lesson.videoFile}
                onFileSelect={(file) => dispatch({ type: 'SET_VIDEO_FILE', chapterIndex, lessonIndex, file })}
                onRemove={() => dispatch({ type: 'SET_VIDEO_FILE', chapterIndex, lessonIndex, file: null })}
              />
            </div>
          )}

          {/* Resources */}
          <div>
            <label className="block text-xs font-medium text-slate-600 mb-1.5">Resources</label>
            {lesson.resources.length > 0 && (
              <div className="space-y-2 mb-3">
                {lesson.resources.map((res, ri) => (
                  <div key={res._id} className="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                    <svg className="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                      <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <span className="flex-1 text-sm text-slate-700 truncate">{res.file.name}</span>
                    <span className="text-xs text-slate-400">{formatFileSize(res.file.size)}</span>
                    <button
                      type="button"
                      onClick={() => dispatch({ type: 'REMOVE_RESOURCE', chapterIndex, lessonIndex, resourceIndex: ri })}
                      className="text-slate-400 hover:text-rose-500 transition-colors"
                    >
                      <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                        <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                ))}
              </div>
            )}
            <label className="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-700 font-medium cursor-pointer transition-colors">
              <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
              Add Resource
              <input
                type="file"
                className="hidden"
                onChange={(e) => {
                  const file = e.target.files?.[0];
                  if (file) dispatch({ type: 'ADD_RESOURCE', chapterIndex, lessonIndex, file });
                  e.target.value = '';
                }}
              />
            </label>
          </div>
        </div>
      </div>
    </div>
  );
}

/* ================================================================
   Chapter Detail Panel (Right Column — chapter overview, no lesson selected)
   ================================================================ */

function ChapterDetailPanel({
  chapter,
  chapterIndex,
  dispatch,
}: {
  chapter: ChapterDraft;
  chapterIndex: number;
  dispatch: React.Dispatch<FormAction>;
}) {
  return (
    <div className="bg-white rounded-xl border border-slate-200 overflow-hidden flex flex-col h-full">
      <div className="flex-1 overflow-y-auto px-6 py-5">
        <div className="flex items-center gap-2 mb-3">
          <span className="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">
            Ch. {chapterIndex + 1}
          </span>
          <span className="text-xs text-slate-400">{chapter.lessons.length} lesson{chapter.lessons.length !== 1 ? 's' : ''}</span>
        </div>
        <input
          type="text"
          value={chapter.title}
          onChange={(e) => dispatch({ type: 'UPDATE_CHAPTER', index: chapterIndex, field: 'title', value: e.target.value })}
          placeholder="Chapter Title"
          className="w-full text-lg font-sora font-semibold text-slate-900 placeholder:text-slate-400 focus:outline-none bg-transparent"
        />
        <input
          type="text"
          value={chapter.description}
          onChange={(e) => dispatch({ type: 'UPDATE_CHAPTER', index: chapterIndex, field: 'description', value: e.target.value })}
          placeholder="Chapter description (optional)"
          className="w-full text-sm text-slate-500 placeholder:text-slate-400 focus:outline-none bg-transparent mt-2"
        />
        <p className="text-sm text-slate-400 mt-6">Select a lesson from the sidebar to edit its content.</p>
      </div>
    </div>
  );
}

/* ================================================================
   Main CurriculumSection Component
   ================================================================ */

export default function CurriculumSection({
  chapters,
  dispatch,
  mode,
  selectedChapterIndex,
  onSelectChapter,
}: CurriculumSectionProps) {
  const [selectedLessonIndex, setSelectedLessonIndex] = useState<number | null>(null);

  const handleAddChapter = () => {
    const newIndex = chapters.length;
    dispatch({ type: 'ADD_CHAPTER' });
    onSelectChapter(newIndex);
    setSelectedLessonIndex(null);
  };

  const handleRemoveChapter = (index: number) => {
    dispatch({ type: 'REMOVE_CHAPTER', index });
    setSelectedLessonIndex(null);
    if (selectedChapterIndex === index) {
      onSelectChapter(chapters.length > 1 ? Math.max(0, index - 1) : null);
    } else if (selectedChapterIndex !== null && selectedChapterIndex > index) {
      onSelectChapter(selectedChapterIndex - 1);
    }
  };

  const handleSelectChapter = (index: number) => {
    if (selectedChapterIndex === index) return;
    onSelectChapter(index);
    setSelectedLessonIndex(null);
  };

  const handleSelectLesson = (chapterIndex: number, lessonIndex: number) => {
    if (selectedChapterIndex !== chapterIndex) {
      onSelectChapter(chapterIndex);
    }
    setSelectedLessonIndex(lessonIndex);
  };

  const handleAddLesson = (chapterIndex: number) => {
    dispatch({ type: 'ADD_LESSON', chapterIndex });
    if (selectedChapterIndex !== chapterIndex) {
      onSelectChapter(chapterIndex);
    }
    const newLessonIndex = chapters[chapterIndex]?.lessons.length ?? 0;
    setSelectedLessonIndex(newLessonIndex);
  };

  const handleRemoveLesson = (chapterIndex: number, lessonIndex: number) => {
    dispatch({ type: 'REMOVE_LESSON', chapterIndex, lessonIndex });
    if (selectedLessonIndex === lessonIndex) {
      const chapter = chapters[chapterIndex];
      if (chapter && chapter.lessons.length > 1) {
        setSelectedLessonIndex(Math.min(lessonIndex, chapter.lessons.length - 2));
      } else {
        setSelectedLessonIndex(null);
      }
    } else if (selectedLessonIndex !== null && selectedLessonIndex > lessonIndex) {
      setSelectedLessonIndex(selectedLessonIndex - 1);
    }
  };

  const activeChapter = selectedChapterIndex !== null ? chapters[selectedChapterIndex] : null;
  const activeLesson = activeChapter && selectedLessonIndex !== null ? activeChapter.lessons[selectedLessonIndex] : null;

  // Build a stable key for the right panel
  const panelKey = selectedChapterIndex !== null
    ? selectedLessonIndex !== null
      ? `lesson-${selectedChapterIndex}-${selectedLessonIndex}`
      : `chapter-${selectedChapterIndex}`
    : 'empty';

  return (
    <section className="bg-slate-50 border-t border-slate-200 px-10 py-8 flex-1">
      {/* Section header */}
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center gap-2">
          <div className="w-1 h-6 bg-indigo-500 rounded-full" />
          <h2 className="font-sora text-lg font-semibold text-slate-900">Curriculum</h2>
        </div>
        <span className="text-sm text-slate-400">{chapters.length} chapter{chapters.length !== 1 ? 's' : ''}</span>
      </div>

      {/* Two-column layout (desktop) */}
      <div className="hidden md:grid md:grid-cols-[320px_1fr] md:gap-6 overflow-hidden" style={{ minHeight: 500, maxHeight: 'calc(100vh - 280px)' }}>
        {/* Left: Chapter sidebar */}
        <div className="min-h-0">
          <ChapterSidebar
            chapters={chapters}
            selectedChapterIndex={selectedChapterIndex}
            selectedLessonIndex={selectedLessonIndex}
            onSelectChapter={handleSelectChapter}
            onSelectLesson={handleSelectLesson}
            onAddChapter={handleAddChapter}
            onRemoveChapter={handleRemoveChapter}
            onAddLesson={handleAddLesson}
            onRemoveLesson={handleRemoveLesson}
          />
        </div>

        {/* Right: Detail panel */}
        <div className="min-h-0">
          <AnimatePresence mode="wait">
            {activeLesson && selectedChapterIndex !== null && selectedLessonIndex !== null ? (
              <motion.div
                key={panelKey}
                initial={{ opacity: 0, x: 12 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: -12 }}
                transition={{ duration: 0.15 }}
                className="h-full"
              >
                <LessonDetailPanel
                  lesson={activeLesson}
                  chapterIndex={selectedChapterIndex}
                  lessonIndex={selectedLessonIndex}
                  dispatch={dispatch}
                  mode={mode}
                />
              </motion.div>
            ) : activeChapter && selectedChapterIndex !== null ? (
              <motion.div
                key={panelKey}
                initial={{ opacity: 0, x: 12 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: -12 }}
                transition={{ duration: 0.15 }}
                className="h-full"
              >
                <ChapterDetailPanel
                  chapter={activeChapter}
                  chapterIndex={selectedChapterIndex}
                  dispatch={dispatch}
                />
              </motion.div>
            ) : (
              <motion.div
                key="empty"
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                exit={{ opacity: 0 }}
                className="bg-white rounded-xl border border-slate-200 flex items-center justify-center h-full"
              >
                <div className="text-center py-20">
                  <div className="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
                    <svg className="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                      <path strokeLinecap="round" strokeLinejoin="round" d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227 7.917-3.286-.672zM12 2.25V4.5m5.834.166l-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243l-1.59-1.59" />
                    </svg>
                  </div>
                  <p className="text-slate-600 font-medium">Select a chapter</p>
                  <p className="text-sm text-slate-400 mt-1">Choose a chapter from the sidebar to manage its lessons</p>
                </div>
              </motion.div>
            )}
          </AnimatePresence>
        </div>
      </div>

      {/* Mobile: stacked layout */}
      <div className="md:hidden">
        <div className="mb-4">
          <div className="flex gap-2 overflow-x-auto pb-2 scrollbar-thin">
            {chapters.map((chapter, ci) => (
              <button
                key={chapter._id}
                type="button"
                onClick={() => { onSelectChapter(ci); setSelectedLessonIndex(null); }}
                className={`shrink-0 px-3 py-2 rounded-lg text-sm font-medium transition-colors ${
                  selectedChapterIndex === ci
                    ? 'bg-indigo-600 text-white'
                    : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
                }`}
              >
                Ch. {ci + 1}: {chapter.title || 'Untitled'}
              </button>
            ))}
            <button
              type="button"
              onClick={handleAddChapter}
              className="shrink-0 px-3 py-2 border-2 border-dashed border-slate-300 rounded-lg text-sm text-slate-500 font-medium hover:border-indigo-400 hover:text-indigo-600 transition-colors"
            >
              + Add
            </button>
          </div>
        </div>

        <AnimatePresence mode="wait">
          {activeLesson && selectedChapterIndex !== null && selectedLessonIndex !== null ? (
            <motion.div
              key={panelKey}
              initial={{ opacity: 0, y: 8 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -8 }}
              transition={{ duration: 0.15 }}
            >
              <LessonDetailPanel
                lesson={activeLesson}
                chapterIndex={selectedChapterIndex}
                lessonIndex={selectedLessonIndex}
                dispatch={dispatch}
                mode={mode}
              />
            </motion.div>
          ) : activeChapter && selectedChapterIndex !== null ? (
            <motion.div
              key={panelKey}
              initial={{ opacity: 0, y: 8 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -8 }}
              transition={{ duration: 0.15 }}
            >
              <ChapterDetailPanel
                chapter={activeChapter}
                chapterIndex={selectedChapterIndex}
                dispatch={dispatch}
              />
            </motion.div>
          ) : (
            <div className="bg-white rounded-xl border border-slate-200 flex items-center justify-center py-16">
              <div className="text-center">
                <p className="text-slate-600 font-medium">Select a chapter</p>
                <p className="text-sm text-slate-400 mt-1">Tap a chapter above to manage its lessons</p>
              </div>
            </div>
          )}
        </AnimatePresence>
      </div>
    </section>
  );
}
